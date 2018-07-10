<div class="jumbotron">
    <div class="container">
        <h1>404 Not Found!</h1>
        <div class="mt-3">
            {if $.en}
                Sorry, but we can`t find requested page.
            {else}
                Извините, но мы не можем найти запрошенную страницу.
            {/if}
        </div>
    </div>
</div>

<div class="container mt-3">
    {'pdoMenu' | snippet : [
        'parents' => 0,
    ]}
</div>