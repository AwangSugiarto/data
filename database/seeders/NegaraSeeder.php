<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NegaraSeeder extends Seeder
{
    public function run(): void
    {
        $negara = [
            // ── Indonesia (sudah ada, skip jika duplikat) ─────────────────────
            ['nama_negara' => 'Indonesia',          'kode_negara' => 'ID'],

            // ── Asia Tenggara ─────────────────────────────────────────────────
            ['nama_negara' => 'Malaysia',            'kode_negara' => 'MY'],
            ['nama_negara' => 'Singapura',           'kode_negara' => 'SG'],
            ['nama_negara' => 'Thailand',            'kode_negara' => 'TH'],
            ['nama_negara' => 'Filipina',            'kode_negara' => 'PH'],
            ['nama_negara' => 'Vietnam',             'kode_negara' => 'VN'],
            ['nama_negara' => 'Myanmar',             'kode_negara' => 'MM'],
            ['nama_negara' => 'Kamboja',             'kode_negara' => 'KH'],
            ['nama_negara' => 'Laos',                'kode_negara' => 'LA'],
            ['nama_negara' => 'Brunei Darussalam',   'kode_negara' => 'BN'],
            ['nama_negara' => 'Timor Leste',         'kode_negara' => 'TL'],

            // ── Asia Selatan ──────────────────────────────────────────────────
            ['nama_negara' => 'India',               'kode_negara' => 'IN'],
            ['nama_negara' => 'Pakistan',            'kode_negara' => 'PK'],
            ['nama_negara' => 'Bangladesh',          'kode_negara' => 'BD'],
            ['nama_negara' => 'Sri Lanka',           'kode_negara' => 'LK'],
            ['nama_negara' => 'Nepal',               'kode_negara' => 'NP'],
            ['nama_negara' => 'Afghanistan',         'kode_negara' => 'AF'],

            // ── Asia Timur ────────────────────────────────────────────────────
            ['nama_negara' => 'China',               'kode_negara' => 'CN'],
            ['nama_negara' => 'Jepang',              'kode_negara' => 'JP'],
            ['nama_negara' => 'Korea Selatan',       'kode_negara' => 'KR'],
            ['nama_negara' => 'Korea Utara',         'kode_negara' => 'KP'],
            ['nama_negara' => 'Taiwan',              'kode_negara' => 'TW'],
            ['nama_negara' => 'Hong Kong',           'kode_negara' => 'HK'],
            ['nama_negara' => 'Mongolia',            'kode_negara' => 'MN'],

            // ── Timur Tengah ──────────────────────────────────────────────────
            ['nama_negara' => 'Arab Saudi',          'kode_negara' => 'SA'],
            ['nama_negara' => 'Uni Emirat Arab',     'kode_negara' => 'AE'],
            ['nama_negara' => 'Kuwait',              'kode_negara' => 'KW'],
            ['nama_negara' => 'Qatar',               'kode_negara' => 'QA'],
            ['nama_negara' => 'Bahrain',             'kode_negara' => 'BH'],
            ['nama_negara' => 'Oman',                'kode_negara' => 'OM'],
            ['nama_negara' => 'Yaman',               'kode_negara' => 'YE'],
            ['nama_negara' => 'Irak',                'kode_negara' => 'IQ'],
            ['nama_negara' => 'Iran',                'kode_negara' => 'IR'],
            ['nama_negara' => 'Yordania',            'kode_negara' => 'JO'],
            ['nama_negara' => 'Suriah',              'kode_negara' => 'SY'],
            ['nama_negara' => 'Lebanon',             'kode_negara' => 'LB'],
            ['nama_negara' => 'Palestina',           'kode_negara' => 'PS'],
            ['nama_negara' => 'Israel',              'kode_negara' => 'IL'],
            ['nama_negara' => 'Turki',               'kode_negara' => 'TR'],

            // ── Afrika ───────────────────────────────────────────────────────
            ['nama_negara' => 'Mesir',               'kode_negara' => 'EG'],
            ['nama_negara' => 'Sudan',               'kode_negara' => 'SD'],
            ['nama_negara' => 'Nigeria',             'kode_negara' => 'NG'],
            ['nama_negara' => 'Ethiopia',            'kode_negara' => 'ET'],
            ['nama_negara' => 'Somalia',             'kode_negara' => 'SO'],
            ['nama_negara' => 'Libya',               'kode_negara' => 'LY'],
            ['nama_negara' => 'Tunisia',             'kode_negara' => 'TN'],
            ['nama_negara' => 'Maroko',              'kode_negara' => 'MA'],
            ['nama_negara' => 'Algeria',             'kode_negara' => 'DZ'],
            ['nama_negara' => 'Afrika Selatan',      'kode_negara' => 'ZA'],
            ['nama_negara' => 'Kenya',               'kode_negara' => 'KE'],
            ['nama_negara' => 'Tanzania',            'kode_negara' => 'TZ'],
            ['nama_negara' => 'Ghana',               'kode_negara' => 'GH'],
            ['nama_negara' => 'Senegal',             'kode_negara' => 'SN'],
            ['nama_negara' => 'Gambia',              'kode_negara' => 'GM'],
            ['nama_negara' => 'Mauritania',          'kode_negara' => 'MR'],
            ['nama_negara' => 'Comoros',             'kode_negara' => 'KM'],

            // ── Asia Tengah ───────────────────────────────────────────────────
            ['nama_negara' => 'Kazakhstan',          'kode_negara' => 'KZ'],
            ['nama_negara' => 'Uzbekistan',          'kode_negara' => 'UZ'],
            ['nama_negara' => 'Tajikistan',          'kode_negara' => 'TJ'],
            ['nama_negara' => 'Kyrgyzstan',          'kode_negara' => 'KG'],
            ['nama_negara' => 'Turkmenistan',        'kode_negara' => 'TM'],

            // ── Eropa ─────────────────────────────────────────────────────────
            ['nama_negara' => 'Rusia',               'kode_negara' => 'RU'],
            ['nama_negara' => 'Inggris',             'kode_negara' => 'GB'],
            ['nama_negara' => 'Jerman',              'kode_negara' => 'DE'],
            ['nama_negara' => 'Prancis',             'kode_negara' => 'FR'],
            ['nama_negara' => 'Belanda',             'kode_negara' => 'NL'],
            ['nama_negara' => 'Italia',              'kode_negara' => 'IT'],
            ['nama_negara' => 'Spanyol',             'kode_negara' => 'ES'],
            ['nama_negara' => 'Belgia',              'kode_negara' => 'BE'],
            ['nama_negara' => 'Swiss',               'kode_negara' => 'CH'],
            ['nama_negara' => 'Swedia',              'kode_negara' => 'SE'],
            ['nama_negara' => 'Norwegia',            'kode_negara' => 'NO'],
            ['nama_negara' => 'Denmark',             'kode_negara' => 'DK'],
            ['nama_negara' => 'Finlandia',           'kode_negara' => 'FI'],
            ['nama_negara' => 'Portugal',            'kode_negara' => 'PT'],
            ['nama_negara' => 'Polandia',            'kode_negara' => 'PL'],
            ['nama_negara' => 'Ukraina',             'kode_negara' => 'UA'],
            ['nama_negara' => 'Yunani',              'kode_negara' => 'GR'],

            // ── Amerika ───────────────────────────────────────────────────────
            ['nama_negara' => 'Amerika Serikat',     'kode_negara' => 'US'],
            ['nama_negara' => 'Kanada',              'kode_negara' => 'CA'],
            ['nama_negara' => 'Brasil',              'kode_negara' => 'BR'],
            ['nama_negara' => 'Meksiko',             'kode_negara' => 'MX'],
            ['nama_negara' => 'Argentina',           'kode_negara' => 'AR'],

            // ── Oseania ───────────────────────────────────────────────────────
            ['nama_negara' => 'Australia',           'kode_negara' => 'AU'],
            ['nama_negara' => 'Selandia Baru',       'kode_negara' => 'NZ'],
            ['nama_negara' => 'Papua Nugini',        'kode_negara' => 'PG'],
        ];

        foreach ($negara as $item) {
            DB::table('dim_warganegara')->updateOrInsert(
                ['nama_negara' => $item['nama_negara']],
                array_merge($item, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('✅ ' . count($negara) . ' negara berhasil di-seed ke dim_warganegara.');
    }
}
