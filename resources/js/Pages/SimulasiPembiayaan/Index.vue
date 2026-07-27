<script setup>
import { computed, reactive } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { formatCurrency } from '@/utils/format';

const props = defineProps({
    defaults: {
        type: Object,
        default: () => ({}),
    },
});

const state = reactive({
    nilai_barang: props.defaults.nilai_barang ?? 150000000,
    dp_mode: props.defaults.dp_mode ?? 'percent',
    dp_percent: props.defaults.dp_percent ?? 20,
    dp_nominal: props.defaults.dp_nominal ?? 30000000,
    tenor_bulan: props.defaults.tenor_bulan ?? 24,
    margin_tahunan: props.defaults.margin_tahunan ?? 18,
    biaya_admin: props.defaults.biaya_admin ?? 2500000,
    biaya_asuransi: props.defaults.biaya_asuransi ?? 1000000,
    metode: props.defaults.metode ?? 'annuity',
});

const nilaiBarang = computed(() => Math.max(0, Number(state.nilai_barang || 0)));
const dpPercent = computed(() => Math.max(0, Number(state.dp_percent || 0)));
const dpNominalInput = computed(() => Math.max(0, Number(state.dp_nominal || 0)));
const tenorBulan = computed(() => Math.max(1, Number(state.tenor_bulan || 1)));
const marginTahunan = computed(() => Math.max(0, Number(state.margin_tahunan || 0)));
const biayaAdmin = computed(() => Math.max(0, Number(state.biaya_admin || 0)));
const biayaAsuransi = computed(() => Math.max(0, Number(state.biaya_asuransi || 0)));

const downPayment = computed(() => {
    if (state.dp_mode === 'nominal') {
        return Math.min(dpNominalInput.value, nilaiBarang.value);
    }

    return Math.min(nilaiBarang.value * (dpPercent.value / 100), nilaiBarang.value);
});

const financedPrincipal = computed(() => {
    return Math.max(0, nilaiBarang.value - downPayment.value);
});

const monthlyRate = computed(() => marginTahunan.value / 12 / 100);

const monthlyInstallment = computed(() => {
    const financed = financedPrincipal.value + biayaAdmin.value + biayaAsuransi.value;

    if (financed <= 0) {
        return 0;
    }

    if (state.metode === 'flat') {
        const flatPrincipal = financed / tenorBulan.value;
        return flatPrincipal + (financed * monthlyRate.value);
    }

    if (monthlyRate.value === 0) {
        return financed / tenorBulan.value;
    }

    const growth = Math.pow(1 + monthlyRate.value, tenorBulan.value);
    return financed * (monthlyRate.value * growth) / (growth - 1);
});

const totalAngsuran = computed(() => monthlyInstallment.value * tenorBulan.value);
const totalPembiayaan = computed(() => downPayment.value + totalAngsuran.value);
const totalBiayaTambahan = computed(() => biayaAdmin.value + biayaAsuransi.value);
const totalBunga = computed(() => Math.max(0, totalAngsuran.value - (financedPrincipal.value + totalBiayaTambahan.value)));

const schedulePreview = computed(() => {
    const rows = [];
    let balance = financedPrincipal.value + totalBiayaTambahan.value;
    const monthsToShow = Math.min(tenorBulan.value, 6);
    const rate = monthlyRate.value;

    for (let month = 1; month <= monthsToShow; month += 1) {
        let interest = 0;
        let principal = 0;
        let payment = 0;

        if (state.metode === 'flat') {
            principal = (financedPrincipal.value + totalBiayaTambahan.value) / tenorBulan.value;
            interest = balance * rate;
            payment = principal + interest;
        } else if (rate === 0) {
            principal = (financedPrincipal.value + totalBiayaTambahan.value) / tenorBulan.value;
            payment = principal;
        } else {
            payment = monthlyInstallment.value;
            interest = balance * rate;
            principal = Math.max(0, payment - interest);
        }

        balance = Math.max(0, balance - principal);
        rows.push({
            month,
            payment,
            interest,
            principal,
            balance,
        });
    }

    return rows;
});

const summaryCards = computed(() => [
    { label: 'Uang Muka', value: formatCurrency(downPayment.value), note: state.dp_mode === 'nominal' ? 'nominal' : `${dpPercent.value}%` },
    { label: 'Dana Dibiayai', value: formatCurrency(financedPrincipal.value + totalBiayaTambahan.value), note: 'pokok + biaya' },
    { label: 'Angsuran/Bulan', value: formatCurrency(monthlyInstallment.value), note: state.metode },
    { label: 'Total Bunga', value: formatCurrency(totalBunga.value), note: `${tenorBulan.value} bulan` },
]);
</script>

<template>
    <Head title="Simulasi Pembiayaan" />

    <AuthenticatedLayout>
        <div class="relative min-h-screen overflow-hidden bg-slate-950 text-white">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute left-[-10rem] top-[-6rem] h-96 w-96 rounded-full bg-blue-400/20 blur-3xl"></div>
                <div class="absolute right-[-8rem] top-24 h-96 w-96 rounded-full bg-red-400/15 blur-3xl"></div>
                <div class="absolute bottom-[-8rem] left-1/3 h-96 w-96 rounded-full bg-red-400/10 blur-3xl"></div>
            </div>

            <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-slate-950/40 backdrop-blur lg:p-10">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.32em] text-blue-200/70">Utility</p>
                            <h1 class="mt-3 text-4xl font-bold leading-tight text-white sm:text-5xl">
                                Simulasi Pembiayaan
                            </h1>
                            <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">
                                Hitung cepat skenario pembiayaan, dari uang muka sampai angsuran bulanan, tanpa meninggalkan dashboard Askarya.
                            </p>
                        </div>

                        <Link :href="route('dashboard')" class="rounded-full border border-white/15 bg-white/5 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10">
                            Kembali ke Dashboard
                        </Link>
                    </div>
                </section>

                <section class="mt-8 grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
                    <article class="rounded-[1.75rem] border border-white/10 bg-slate-900/80 p-6 shadow-xl shadow-slate-950/30 backdrop-blur">
                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label class="text-xs uppercase tracking-[0.22em] text-slate-400">Nilai Barang</label>
                                <input v-model.number="state.nilai_barang" type="number" min="0" class="mt-2 block w-full rounded-2xl border border-white/10 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-500 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/20">
                            </div>

                            <div>
                                <label class="text-xs uppercase tracking-[0.22em] text-slate-400">Metode</label>
                                <select v-model="state.metode" class="mt-2 block w-full rounded-2xl border border-white/10 bg-slate-950 px-4 py-3 text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/20">
                                    <option value="annuity">Annuity</option>
                                    <option value="flat">Flat</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-xs uppercase tracking-[0.22em] text-slate-400">Mode DP</label>
                                <select v-model="state.dp_mode" class="mt-2 block w-full rounded-2xl border border-white/10 bg-slate-950 px-4 py-3 text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/20">
                                    <option value="percent">Persen</option>
                                    <option value="nominal">Nominal</option>
                                </select>
                            </div>

                            <div v-if="state.dp_mode === 'percent'">
                                <label class="text-xs uppercase tracking-[0.22em] text-slate-400">DP Percent</label>
                                <input v-model.number="state.dp_percent" type="number" min="0" max="100" step="0.01" class="mt-2 block w-full rounded-2xl border border-white/10 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-500 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/20">
                            </div>

                            <div v-else>
                                <label class="text-xs uppercase tracking-[0.22em] text-slate-400">DP Nominal</label>
                                <input v-model.number="state.dp_nominal" type="number" min="0" class="mt-2 block w-full rounded-2xl border border-white/10 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-500 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/20">
                            </div>

                            <div>
                                <label class="text-xs uppercase tracking-[0.22em] text-slate-400">Tenor (bulan)</label>
                                <input v-model.number="state.tenor_bulan" type="number" min="1" class="mt-2 block w-full rounded-2xl border border-white/10 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-500 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/20">
                            </div>

                            <div>
                                <label class="text-xs uppercase tracking-[0.22em] text-slate-400">Margin Tahunan (%)</label>
                                <input v-model.number="state.margin_tahunan" type="number" min="0" step="0.01" class="mt-2 block w-full rounded-2xl border border-white/10 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-500 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/20">
                            </div>

                            <div>
                                <label class="text-xs uppercase tracking-[0.22em] text-slate-400">Biaya Admin</label>
                                <input v-model.number="state.biaya_admin" type="number" min="0" class="mt-2 block w-full rounded-2xl border border-white/10 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-500 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/20">
                            </div>

                            <div>
                                <label class="text-xs uppercase tracking-[0.22em] text-slate-400">Biaya Asuransi</label>
                                <input v-model.number="state.biaya_asuransi" type="number" min="0" class="mt-2 block w-full rounded-2xl border border-white/10 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-500 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/20">
                            </div>
                        </div>
                    </article>

                    <article class="rounded-[1.75rem] border border-white/10 bg-blue-950 p-6 shadow-xl shadow-slate-950/30 backdrop-blur">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div v-for="card in summaryCards" :key="card.label" class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ card.label }}</p>
                                <p class="mt-2 text-2xl font-semibold text-white">{{ card.value }}</p>
                                <p class="mt-1 text-sm text-slate-300">{{ card.note }}</p>
                            </div>
                        </div>

                        <div class="mt-5 rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-5">
                            <p class="text-xs uppercase tracking-[0.25em] text-blue-200/70">Ringkasan Cepat</p>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-2xl bg-white/5 p-4">
                                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Total Pembiayaan</p>
                                    <p class="mt-2 text-xl font-semibold text-white">{{ formatCurrency(totalPembiayaan) }}</p>
                                </div>
                                <div class="rounded-2xl bg-white/5 p-4">
                                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Dana Bersih</p>
                                    <p class="mt-2 text-xl font-semibold text-white">{{ formatCurrency(financedPrincipal + biayaAdmin + biayaAsuransi) }}</p>
                                </div>
                            </div>
                        </div>
                    </article>
                </section>

                <section class="mt-8 grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
                    <article class="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-xl shadow-slate-950/30 backdrop-blur">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs uppercase tracking-[0.25em] text-blue-200/70">Preview Angsuran</p>
                                <h2 class="mt-2 text-2xl font-semibold text-white">6 Bulan Pertama</h2>
                            </div>
                        </div>

                        <div class="mt-5 space-y-3">
                            <div v-for="row in schedulePreview" :key="row.month" class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Bulan {{ row.month }}</p>
                                        <p class="mt-1 text-sm text-slate-300">
                                            Pokok: {{ formatCurrency(row.principal) }} · Bunga: {{ formatCurrency(row.interest) }}
                                        </p>
                                    </div>
                                    <p class="text-lg font-semibold text-white">{{ formatCurrency(row.payment) }}</p>
                                </div>
                                <div class="mt-3 h-2 rounded-full bg-white/10">
                                    <div class="h-2 rounded-full bg-blue-400" :style="{ width: `${Math.max(10, Math.min(100, (1 - (row.balance / (financedPrincipal + totalBiayaTambahan || 1))) * 100))}%` }"></div>
                                </div>
                            </div>
                        </div>
                    </article>

                    <article class="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-xl shadow-slate-950/30 backdrop-blur">
                        <div class="flex flex-wrap items-end justify-between gap-4">
                            <div>
                                <p class="text-xs uppercase tracking-[0.25em] text-blue-200/70">Peta Nilai</p>
                                <h2 class="mt-2 text-2xl font-semibold text-white">Komposisi Pembiayaan</h2>
                            </div>
                            <p class="text-sm text-slate-300">DP, biaya, dan tenor dihitung real-time.</p>
                        </div>

                        <div class="mt-5 grid gap-4 sm:grid-cols-3">
                            <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">DP</p>
                                <p class="mt-2 text-xl font-semibold text-white">{{ formatCurrency(downPayment) }}</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Biaya Tambahan</p>
                                <p class="mt-2 text-xl font-semibold text-white">{{ formatCurrency(totalBiayaTambahan) }}</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Tenor</p>
                                <p class="mt-2 text-xl font-semibold text-white">{{ tenorBulan }} bulan</p>
                            </div>
                        </div>

                        <div class="mt-5 rounded-[1.5rem] border border-red-300 bg-red-600 p-5">
                            <p class="text-xs uppercase tracking-[0.25em] text-white/80">Catatan</p>
                            <p class="mt-3 text-sm leading-7 text-white">
                                Perhitungan ini cocok untuk skenario penawaran internal. Jika Anda mau, kita bisa lanjut bikin versi yang bisa disimpan sebagai template simulasi atau diekspor ke PDF.
                            </p>
                        </div>
                    </article>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
