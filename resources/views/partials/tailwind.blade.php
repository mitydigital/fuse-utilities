@if ($environment === 'local')
    <div class="fixed bottom-0 left-0 z-50 rounded-tr bg-black p-2 text-center font-mono text-xs text-white opacity-50 hover:opacity-100">
        <span class="sm:hidden">mobile</span>
        <span class="hidden sm:inline md:hidden">sm</span>
        <span class="hidden md:inline lg:hidden">md</span>
        <span class="hidden lg:inline xl:hidden">lg</span>
        <span class="hidden xl:inline 2xl:hidden">xl</span>
        <span class="3xl:hidden hidden 2xl:inline">2xl</span>
        <span class="3xl:inline 4xl:hidden hidden">3xl</span>
        <span class="4xl:inline hidden">4xl+</span>
    </div>
@endif
