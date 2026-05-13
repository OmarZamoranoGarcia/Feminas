<footer class="bg-body-tertiary text-center py-4 mt-5 border-top">
    <div class="container">
        <p class="mb-0">&copy; {{ date('Y') }} DevMart. Todos los derechos reservados.</p>
        @isset($userCount)
            <p class="text-muted small">Únete a nuestros {{ $userCount }} usuarios.</p>
        @endisset
    </div>
</footer>