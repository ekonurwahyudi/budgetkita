<footer class="kt-footer">
    <!-- Container -->
    <div class="kt-container-fixed">
        <div class="flex flex-col md:flex-row justify-center md:justify-between items-center gap-3 py-5">
            <div class="flex order-2 md:order-1 gap-2 font-normal text-sm">
                <span class="text-secondary-foreground">
                    {{ date('Y') }}&copy;
                </span>
                <a class="text-secondary-foreground hover:text-primary" href="{{ route('dashboard') }}">
                    BudgetKita
                </a>
            </div>
            <nav class="flex order-1 md:order-2 gap-4 font-normal text-sm text-secondary-foreground">
                <span>Aplikasi Pencatatan Keuangan Tambak Udang</span>
            </nav>
        </div>
    </div>
    <!-- End of Container -->
</footer>
