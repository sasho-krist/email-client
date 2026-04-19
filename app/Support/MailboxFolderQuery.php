<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

final class MailboxFolderQuery
{
    public static function sortParameter(Request $request): string
    {
        $sort = $request->query('sort', 'date_desc');

        return in_array($sort, ['date_desc', 'date_asc'], true) ? $sort : 'date_desc';
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $messages
     * @return Collection<int, array<string, mixed>>
     */
    public static function filterAndSort(Collection $messages, Request $request): Collection
    {
        $sort = self::sortParameter($request);
        $sender = mb_strtolower(trim((string) $request->query('sender', '')));
        $email = mb_strtolower(trim((string) $request->query('email', '')));

        $filtered = $messages->filter(function (array $m) use ($sender, $email): bool {
            if ($sender !== '' && ! self::matchesSenderFilter($m, $sender)) {
                return false;
            }
            if ($email !== '' && ! self::matchesEmailFilter($m, $email)) {
                return false;
            }

            return true;
        });

        return self::sortByDate($filtered, $sort);
    }

    /**
     * @param  array<string, mixed>  $m
     */
    protected static function matchesSenderFilter(array $m, string $senderLower): bool
    {
        $name = mb_strtolower((string) ($m['from_name'] ?? ''));
        if ($name !== '') {
            return str_contains($name, $senderLower);
        }

        return str_contains(mb_strtolower((string) ($m['from'] ?? '')), $senderLower);
    }

    /**
     * @param  array<string, mixed>  $m
     */
    protected static function matchesEmailFilter(array $m, string $emailLower): bool
    {
        $mail = mb_strtolower((string) ($m['from_mail'] ?? ''));
        if ($mail !== '') {
            return str_contains($mail, $emailLower);
        }

        return str_contains(mb_strtolower((string) ($m['from'] ?? '')), $emailLower);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $messages
     * @return Collection<int, array<string, mixed>>
     */
    public static function sortByDate(Collection $messages, string $sort): Collection
    {
        $desc = $sort === 'date_desc';
        $withTs = $messages->map(function (array $m) use ($desc): array {
            $date = $m['date'] ?? null;
            $ts = $date instanceof Carbon ? $date->timestamp : null;
            $m['_ts'] = $ts ?? ($desc ? -1 : PHP_INT_MAX);

            return $m;
        });

        $sorted = $desc
            ? $withTs->sortByDesc('_ts')
            : $withTs->sortBy('_ts');

        return $sorted
            ->map(static function (array $m): array {
                unset($m['_ts']);

                return $m;
            })
            ->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $messages
     * @return Collection<string, Collection<int, array<string, mixed>>>
     */
    public static function groupByDateSorted(Collection $messages, string $sort): Collection
    {
        $grouped = $messages->groupBy(function (array $m): string {
            $d = $m['date'] ?? null;

            return $d ? $d->format('Y-m-d') : '__nodate__';
        });

        $noDate = $grouped->pull('__nodate__', collect());

        $dated = $sort === 'date_desc'
            ? $grouped->sortKeysDesc()
            : $grouped->sortKeys();

        $dated = $dated->map(fn (Collection $items) => self::sortByDate($items, $sort));

        if ($noDate->isNotEmpty()) {
            $dated->put('__nodate__', self::sortByDate($noDate, $sort));
        }

        return $dated;
    }
}
