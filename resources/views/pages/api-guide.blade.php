<x-guest-layout wide>
    <x-slot name="title">Упътване за API</x-slot>
    <article class="prose prose-slate max-w-none text-slate-800 dark:prose-invert dark:text-slate-100 prose-headings:scroll-mt-24 prose-code:rounded prose-code:bg-slate-100 prose-code:px-1 prose-code:py-0.5 prose-code:text-sm prose-code:before:content-none prose-code:after:content-none dark:prose-code:bg-slate-800 dark:prose-code:text-slate-100 prose-pre:bg-slate-800 prose-pre:text-slate-100 dark:prose-pre:bg-slate-950">
        <h1>Упътване за REST API</h1>
        <p>
            Базов адрес на API: <strong><code>{{ url('/api/v1') }}</code></strong>.
            Отговорите са JSON с обща структура: при успех <code>success: true</code> и поле <code>data</code>;
            при грешка <code>success: false</code>, съобщение в <code>message</code> и при валидационни грешки — обект <code>errors</code>.
        </p>

        <h2>Удостоверяване (Laravel Sanctum)</h2>
        <ol>
            <li>Изпратете <code>POST {{ url('/api/v1/login') }}</code> с JSON тяло (вижте по-долу).</li>
            <li>В отговора вземете стойността <code>data.token</code>.</li>
            <li>За всички защитени заявки добавете заглавка: <code>Authorization: Bearer ВАШИЯТ_ТОКЕН</code>.</li>
            <li>Препоръчително е и <code>Accept: application/json</code>.</li>
        </ol>

        <h3><code>POST /api/v1/login</code></h3>
        <p>Без авторизационна заглавка.</p>
        <pre><code>{
  "email": "user@example.com",
  "password": "вашата-парола",
  "device_name": "опционално име на устройство или клиент"
}</code></pre>
        <p>При успех в <code>data</code> се връщат <code>token</code>, <code>token_type</code> (Bearer) и обект <code>user</code>.</p>

        <h3><code>POST /api/v1/logout</code></h3>
        <p>Изисква Bearer токен. Инвалидира текущия токен.</p>

        <h3><code>GET /api/v1/user</code></h3>
        <p>Връща текущия потребител в <code>data.user</code>: <code>id</code>, <code>name</code>, <code>first_name</code>, <code>email</code>, <code>email_verified_at</code>, <code>avatar_url</code> (или <code>null</code>).</p>

        <hr class="my-8 border-slate-200 dark:border-slate-700" />

        <h2>Откриване на пощенски настройки</h2>
        <h3><code>POST /api/v1/mail/discover</code></h3>
        <p>Тяло: <code>{ "email": "user@domain.com" }</code>. При успех — открити IMAP/SMTP параметри в <code>data.discovery</code>.</p>

        <hr class="my-8 border-slate-200 dark:border-slate-700" />

        <h2>Имейл акаунти</h2>
        <p>В маршрутите <code>{account}</code> е числовият идентификатор на акаунта в приложението (не имейл адресът).</p>

        <h3><code>GET /api/v1/email-accounts</code></h3>
        <p>Списък на акаунтите на потребителя: <code>data.email_accounts</code>.</p>

        <h3><code>POST /api/v1/email-accounts</code></h3>
        <p>Създаване на акаунт — същите полета като в уеб формата:</p>
        <ul>
            <li><code>manual</code> — <code>true</code> за ръчни IMAP/SMTP полета, иначе автоматично откриване по имейл.</li>
            <li>Задължителни: <code>email</code>, <code>mailbox_password</code>.</li>
            <li>При <code>manual: true</code>: <code>imap_host</code>, <code>imap_port</code>, <code>imap_security</code>, <code>imap_auth</code>, <code>smtp_host</code>, <code>smtp_port</code>, <code>smtp_security</code>, <code>smtp_auth</code> и др. опционални полета като в уеб API.</li>
        </ul>

        <h3><code>DELETE /api/v1/email-accounts/{account}</code></h3>
        <p>Премахва акаунта на текущия потребител.</p>

        <hr class="my-8 border-slate-200 dark:border-slate-700" />

        <h2>Пощенски папки и съобщения</h2>
        <p><code>{folder}</code> е едно от: <code>inbox</code>, <code>sent</code>, <code>spam</code>, <code>trash</code>.</p>

        <h3><code>GET /api/v1/email-accounts/{account}/folders/{folder}/messages</code></h3>
        <p>
            Списък съобщения с преглед (заглавие, подател, дата, <code>from_name</code>, <code>from_mail</code> и др.).
            Опционални query параметри: <code>sort=date_desc|date_asc</code> (по подразбиране <code>date_desc</code> — най-новите отгоре),
            <code>sender</code> — част от името на изпращача, <code>email</code> — част от имейл адреса (филтърът се прилага върху последно заредения резултат от сървъра).
        </p>

        <h3><code>GET /api/v1/email-accounts/{account}/folders/{folder}/messages/{uid}</code></h3>
        <p>Пълно съобщение по IMAP UID — включително <code>body_html</code> / <code>body_text</code>.</p>

        <h3><code>DELETE /api/v1/email-accounts/{account}/folders/{folder}/messages/{uid}</code></h3>
        <p>Изтриване/обработка на съобщението според настройките на акаунта.</p>

        <hr class="my-8 border-slate-200 dark:border-slate-700" />

        <h2>Настройки на пощата</h2>
        <h3><code>PATCH /api/v1/mail/settings</code></h3>
        <p>
            Поле <code>tab</code> определя раздела: <code>server</code>, <code>signature</code>, <code>profile</code>, <code>display</code>, <code>reply</code>.
            По избор се подава <code>account</code> като query или в тялото — ID на имейл акаунта; без това се ползва първият акаунт на потребителя.
        </p>
        <ul>
            <li><strong>server</strong> — IMAP/SMTP полета и опционално нова <code>mailbox_password</code>.</li>
            <li><strong>signature</strong> — <code>signature_html</code>, <code>signature_use_html</code>.</li>
            <li><strong>profile</strong> — име на профил, цвят, показвано име, reply-to, организация.</li>
            <li><strong>display</strong> — <code>inbox_group_by</code>: <code>none</code> или <code>date</code>.</li>
            <li><strong>reply</strong> — булеви <code>reply_include_quote</code>, <code>reply_top_posting</code> в JSON тялото.</li>
        </ul>
        <p>При успех се връща обновеният акаунт в <code>data.email_account</code>.</p>

        <hr class="my-8 border-slate-200 dark:border-slate-700" />

        <h2>Пример с curl (вход)</h2>
        <pre><code>curl -X POST "{{ url('/api/v1/login') }}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"email\":\"you@example.com\",\"password\":\"secret\",\"device_name\":\"curl\"}"</code></pre>

        <p class="mt-8">
            <a href="{{ route('home') }}" class="font-medium text-blue-600 hover:underline dark:text-blue-400">← Начало</a>
        </p>
    </article>
</x-guest-layout>
