<?php

use App\Http\Controllers\PenawaranController;
use App\Http\Controllers\PurchasingOrderController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SuratJalanController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\MitraController;
use App\Http\Controllers\DocumentTemplateController;
use App\Http\Controllers\NotaTokoController;
use App\Http\Controllers\BeritaAcaraController;
use App\Http\Controllers\FakturPajakController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

$documentModules = [
    'penawaran' => [
        'title' => 'Surat Penawaran',
        'summary' => 'Pintu masuk transaksi. Dari draft, submitted, approved, lalu turun ke invoice.',
        'stage' => 'Core workflow',
        'primary_route' => 'penawaran.index',
        'secondary_route' => 'penawaran.create',
    ],
    'purchasing-order' => [
        'title' => 'Purchasing Order',
        'summary' => 'Tempat upload PO setelah penawaran approved sebelum invoice dibuat.',
        'stage' => 'Approval bridge',
        'primary_route' => 'purchasing-order.index',
        'secondary_route' => null,
    ],
    'invoice' => [
        'title' => 'Invoice',
        'summary' => 'Dokumen tagihan utama, menjadi sumber untuk surat jalan, berita acara, dan faktur pajak.',
        'stage' => 'Billing',
        'primary_route' => 'invoice.index',
        'secondary_route' => null,
    ],
    'surat-jalan' => [
        'title' => 'Surat Jalan',
        'summary' => 'Turunan dari invoice untuk kebutuhan pengiriman dan arsip operasional.',
        'stage' => 'Delivery',
        'primary_route' => 'surat-jalan.index',
        'secondary_route' => null,
    ],
    'berita-acara' => [
        'title' => 'Berita Acara',
        'summary' => 'Dokumen serah terima atau konfirmasi selesai pekerjaan yang mengikuti invoice.',
        'stage' => 'Closing',
        'primary_route' => 'berita-acara.index',
        'secondary_route' => 'berita-acara.create',
    ],
    'faktur-pajak' => [
        'title' => 'Faktur Pajak',
        'summary' => 'Pengarsipan faktur pajak dan penanda status pembayaran pajak.',
        'stage' => 'Tax',
        'primary_route' => 'faktur-pajak.index',
        'secondary_route' => 'faktur-pajak.create',
    ],
    'nota-toko' => [
        'title' => 'Nota Toko',
        'summary' => 'Transaksi penjualan retail yang berdiri sendiri dari alur invoice utama.',
        'stage' => 'Retail',
        'primary_route' => 'nota-toko.index',
        'secondary_route' => 'nota-toko.create',
    ],
    'customers' => [
        'title' => 'Customers',
        'summary' => 'Master data pelanggan untuk penawaran dan pengiriman dokumen.',
        'stage' => 'Master data',
        'primary_route' => 'customers.index',
        'secondary_route' => null,
    ],
    'mitra' => [
        'title' => 'Mitra',
        'summary' => 'Master data partner atau vendor yang punya nomor dokumen khusus.',
        'stage' => 'Master data',
        'primary_route' => 'mitra.index',
        'secondary_route' => null,
    ],
    'users' => [
        'title' => 'Users',
        'summary' => 'Pengelolaan akun admin dan superadmin.',
        'stage' => 'Access control',
        'primary_route' => 'users.index',
        'secondary_route' => null,
    ],
    'document-templates' => [
        'title' => 'Document Templates',
        'summary' => 'Template PDF per perusahaan agar output dokumen mudah disesuaikan.',
        'stage' => 'Layout system',
        'primary_route' => 'document-templates.index',
        'secondary_route' => 'document-templates.create',
    ],
    'simulasi-pembiayaan' => [
        'title' => 'Simulasi Pembiayaan',
        'summary' => 'Kalkulator pendukung untuk skenario biaya dan cicilan.',
        'stage' => 'Utility',
        'primary_route' => 'simulasi-pembiayaan.index',
        'secondary_route' => null,
    ],
];

$renderModule = function (string $key, string $mode = 'index', array $extra = []) use ($documentModules) {
    $module = $documentModules[$key] ?? [
        'title' => ucfirst(str_replace('-', ' ', $key)),
        'summary' => 'Halaman placeholder untuk modul ini.',
        'stage' => 'Placeholder',
        'primary_route' => null,
        'secondary_route' => null,
    ];

    return Inertia::render('Flow/Module', array_merge($module, [
        'module_key' => $key,
        'mode' => $mode,
    ], $extra));
};

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', function () use ($documentModules) {
    return Inertia::render('Dashboard', [
        'modules' => $documentModules,
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () use ($renderModule) {
    Route::get('/penawaran', [PenawaranController::class, 'index'])->name('penawaran.index');
    Route::get('/penawaran/create', [PenawaranController::class, 'create'])->name('penawaran.create');
    Route::post('/penawaran', [PenawaranController::class, 'store'])->name('penawaran.store');
    Route::get('/penawaran/{penawaran}', [PenawaranController::class, 'show'])->name('penawaran.show');
    Route::get('/penawaran/{penawaran}/preview-pdf', [PenawaranController::class, 'previewPdf'])->name('penawaran.preview-pdf');
    Route::get('/penawaran/{penawaran}/edit', [PenawaranController::class, 'edit'])->name('penawaran.edit');
    Route::put('/penawaran/{penawaran}', [PenawaranController::class, 'update'])->name('penawaran.update');
    Route::delete('/penawaran/{penawaran}', [PenawaranController::class, 'destroy'])->name('penawaran.destroy');
    Route::post('/penawaran/{penawaran}/approve', [PenawaranController::class, 'approveForInvoice'])->name('penawaran.approve');

    Route::get('/purchasing-order', [PurchasingOrderController::class, 'index'])->name('purchasing-order.index');
    Route::post('/purchasing-order', [PurchasingOrderController::class, 'store'])->name('purchasing-order.store');
    Route::post('/purchasing-order/{penawaran}/create-invoice', [PurchasingOrderController::class, 'createInvoice'])->name('purchasing-order.create-invoice');
    Route::post('/purchasing-order/{penawaran}/next-invoice', [PurchasingOrderController::class, 'nextInvoice'])->name('purchasing-order.next-invoice');
    Route::post('/purchasing-order/{penawaran}/cancel', [PurchasingOrderController::class, 'cancelApproved'])->name('purchasing-order.cancel');
    Route::get('/purchasing-order/{purchasingOrder}/preview', [PurchasingOrderController::class, 'preview'])->name('purchasing-order.preview');

    Route::get('/invoice', [InvoiceController::class, 'index'])->name('invoice.index');
    Route::get('/invoice/{invoice}', [InvoiceController::class, 'show'])->name('invoice.show');
    Route::get('/invoice/{invoice}/preview-pdf', [InvoiceController::class, 'previewPdf'])->name('invoice.preview-pdf');
    Route::post('/invoice/{invoice}/update-print-date', [InvoiceController::class, 'updatePrintDate'])->name('invoice.update-print-date');
    Route::post('/invoice/{invoice}/verify-payment', [InvoiceController::class, 'verifyPayment'])->name('invoice.verify-payment');
    Route::delete('/invoice/{invoice}', [InvoiceController::class, 'destroy'])->name('invoice.destroy');

    Route::get('/surat-jalan', [SuratJalanController::class, 'index'])->name('surat-jalan.index');
    Route::get('/surat-jalan/create', [SuratJalanController::class, 'create'])->name('surat-jalan.create');
    Route::post('/surat-jalan', [SuratJalanController::class, 'store'])->name('surat-jalan.store');
    Route::get('/surat-jalan/{suratJalan}', [SuratJalanController::class, 'show'])->name('surat-jalan.show');
    Route::get('/surat-jalan/{suratJalan}/preview', [SuratJalanController::class, 'preview'])->name('surat-jalan.preview');

    Route::get('/berita-acara', [BeritaAcaraController::class, 'index'])->name('berita-acara.index');
    Route::get('/berita-acara/create', [BeritaAcaraController::class, 'create'])->name('berita-acara.create');
    Route::post('/berita-acara', [BeritaAcaraController::class, 'store'])->name('berita-acara.store');
    Route::get('/berita-acara/{beritaAcara}', [BeritaAcaraController::class, 'show'])->name('berita-acara.show');
    Route::get('/berita-acara/{beritaAcara}/preview', [BeritaAcaraController::class, 'preview'])->name('berita-acara.preview');
    Route::get('/berita-acara/{beritaAcara}/edit', [BeritaAcaraController::class, 'edit'])->name('berita-acara.edit');
    Route::put('/berita-acara/{beritaAcara}', [BeritaAcaraController::class, 'update'])->name('berita-acara.update');
    Route::delete('/berita-acara/{beritaAcara}', [BeritaAcaraController::class, 'destroy'])->name('berita-acara.destroy');

    Route::get('/faktur-pajak', [FakturPajakController::class, 'index'])->name('faktur-pajak.index');
    Route::get('/faktur-pajak/create', [FakturPajakController::class, 'create'])->name('faktur-pajak.create');
    Route::post('/faktur-pajak', [FakturPajakController::class, 'store'])->name('faktur-pajak.store');
    Route::get('/faktur-pajak/{fakturPajak}', [FakturPajakController::class, 'show'])->name('faktur-pajak.show');
    Route::get('/faktur-pajak/{fakturPajak}/preview', [FakturPajakController::class, 'preview'])->name('faktur-pajak.preview');
    Route::get('/faktur-pajak/{fakturPajak}/edit', [FakturPajakController::class, 'edit'])->name('faktur-pajak.edit');
    Route::put('/faktur-pajak/{fakturPajak}', [FakturPajakController::class, 'update'])->name('faktur-pajak.update');
    Route::delete('/faktur-pajak/{fakturPajak}', [FakturPajakController::class, 'destroy'])->name('faktur-pajak.destroy');

    Route::get('/nota-toko', [NotaTokoController::class, 'index'])->name('nota-toko.index');
    Route::get('/nota-toko/create', [NotaTokoController::class, 'create'])->name('nota-toko.create');
    Route::post('/nota-toko', [NotaTokoController::class, 'store'])->name('nota-toko.store');
    Route::get('/nota-toko/{notaToko}', [NotaTokoController::class, 'show'])->name('nota-toko.show');
    Route::get('/nota-toko/{notaToko}/preview-pdf', [NotaTokoController::class, 'previewPdf'])->name('nota-toko.preview-pdf');
    Route::get('/nota-toko/{notaToko}/edit', [NotaTokoController::class, 'edit'])->name('nota-toko.edit');
    Route::put('/nota-toko/{notaToko}', [NotaTokoController::class, 'update'])->name('nota-toko.update');
    Route::delete('/nota-toko/{notaToko}', [NotaTokoController::class, 'destroy'])->name('nota-toko.destroy');

    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    Route::get('/mitra', [MitraController::class, 'index'])->name('mitra.index');
    Route::get('/mitra/create', [MitraController::class, 'create'])->name('mitra.create');
    Route::post('/mitra', [MitraController::class, 'store'])->name('mitra.store');
    Route::get('/mitra/{mitra}', [MitraController::class, 'show'])->name('mitra.show');
    Route::get('/mitra/{mitra}/edit', [MitraController::class, 'edit'])->name('mitra.edit');
    Route::put('/mitra/{mitra}', [MitraController::class, 'update'])->name('mitra.update');
    Route::delete('/mitra/{mitra}', [MitraController::class, 'destroy'])->name('mitra.destroy');
    Route::get('/users', fn () => $renderModule('users'))->name('users.index');
    Route::get('/document-templates', [DocumentTemplateController::class, 'index'])->name('document-templates.index');
    Route::get('/document-templates/create', [DocumentTemplateController::class, 'create'])->name('document-templates.create');
    Route::post('/document-templates', [DocumentTemplateController::class, 'store'])->name('document-templates.store');
    Route::get('/document-templates/preview', [DocumentTemplateController::class, 'preview'])->name('document-templates.preview');
    Route::get('/document-templates/{documentTemplate}', [DocumentTemplateController::class, 'show'])->name('document-templates.show');
    Route::get('/document-templates/{documentTemplate}/edit', [DocumentTemplateController::class, 'edit'])->name('document-templates.edit');
    Route::put('/document-templates/{documentTemplate}', [DocumentTemplateController::class, 'update'])->name('document-templates.update');
    Route::delete('/document-templates/{documentTemplate}', [DocumentTemplateController::class, 'destroy'])->name('document-templates.destroy');
    Route::get('/simulasi-pembiayaan', function () {
        return Inertia::render('SimulasiPembiayaan/Index', [
            'defaults' => [
                'nilai_barang' => 150000000,
                'dp_mode' => 'percent',
                'dp_percent' => 20,
                'dp_nominal' => 30000000,
                'tenor_bulan' => 24,
                'margin_tahunan' => 18,
                'biaya_admin' => 2500000,
                'biaya_asuransi' => 1000000,
                'metode' => 'annuity',
            ],
        ]);
    })->name('simulasi-pembiayaan.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
