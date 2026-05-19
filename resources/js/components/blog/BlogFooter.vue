<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <footer class="mt-16 bg-[#0a0a0a] text-white">
        <div class="mx-auto max-w-7xl px-4 py-12 md:px-6">
            <div class="flex flex-col gap-8 md:flex-row md:items-start md:justify-between">
                <div>
                    <Link href="/" class="flex items-center gap-2">
                        <span
                            class="bg-[#e10600] px-2 py-1 text-xs font-black tracking-widest uppercase"
                            >Auto</span
                        >
                        <span class="text-xl font-black tracking-tight uppercase"
                            >News</span
                        >
                    </Link>
                    <p class="mt-4 max-w-sm text-sm text-white/60">
                        O portal brasileiro sobre carros elétricos, híbridos e
                        mobilidade sustentável. Notícias, análises e tendências
                        do mercado automotivo.
                    </p>
                </div>
                <div class="grid grid-cols-2 gap-8 text-sm">
                    <div>
                        <h3 class="mb-3 font-bold tracking-wide text-[#e10600] uppercase">
                            Seções
                        </h3>
                        <ul class="space-y-2 text-white/70">
                            <li>
                                <Link href="/" class="hover:text-white">Início</Link>
                            </li>
                            <li>
                                <Link
                                    href="/categoria/veiculos-eletricos"
                                    class="hover:text-white"
                                    >Elétricos</Link
                                >
                            </li>
                            <li>
                                <Link
                                    href="/categoria/mercado"
                                    class="hover:text-white"
                                    >Mercado</Link
                                >
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="mb-3 font-bold tracking-wide text-[#e10600] uppercase">
                            Conta
                        </h3>
                        <ul class="space-y-2 text-white/70">
                            <li>
                                <Link href="/login" class="hover:text-white">Entrar</Link>
                            </li>
                            <li>
                                <Link href="/admin/posts" class="hover:text-white"
                                    >Administrar</Link
                                >
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div
                class="mt-10 border-t border-white/10 pt-6 text-center text-xs text-white/50"
            >
                © {{ new Date().getFullYear() }} Auto News. Todos os direitos
                reservados.
            </div>
        </div>
    </footer>
</template>
