@if($environment === 'local')
<div class="fixed z-50 bottom-0 left-0 text-center text-white rounded-tr bg-black p-2 text-xs font-mono opacity-50 hover:opacity-100">
    <span class="sm:hidden">mobile</span>
    <span class="hidden sm:inline md:hidden">sm</span>
    <span class="hidden md:inline lg:hidden">md</span>
    <span class="hidden lg:inline xl:hidden">lg</span>
    <span class="hidden xl:inline 2xl:hidden">xl</span>
    <span class="hidden 2xl:inline 3xl:hidden">2xl</span>
    <span class="hidden 3xl:inline 4xl:hidden">3xl</span>
    <span class="hidden 4xl:inline">4xl+</span>
</div>
@endif