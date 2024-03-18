<?php

namespace App\DataFixtures\Languages;

use App\Entity\Language;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class LanguageFixtures extends Fixture implements FixtureGroupInterface {

    public static function getGroups(): array {
        return ['languages'];
    }

    public function load(ObjectManager $manager) {

        $languages_en = array(
            'en' => 'English',
            'aa' => 'Afar',
            'ab' => 'Abkhazian',
            'af' => 'Afrikaans',
            'am' => 'Amharic',
            'ar' => 'Arabic',
            'as' => 'Assamese',
            'ay' => 'Aymara',
            'az' => 'Azerbaijani',
            'ba' => 'Bashkir',
            'be' => 'Byelorussian',
            'bg' => 'Bulgarian',
            'bh' => 'Bihari',
            'bi' => 'Bislama',
            'bn' => 'Bengali/Bangla',
            'bo' => 'Tibetan',
            'br' => 'Breton',
            'ca' => 'Catalan',
            'co' => 'Corsican',
            'cs' => 'Czech',
            'cy' => 'Welsh',
            'da' => 'Danish',
            'de' => 'German',
            'dz' => 'Bhutani',
            'el' => 'Greek',
            'eo' => 'Esperanto',
            'es' => 'Spanish',
            'et' => 'Estonian',
            'eu' => 'Basque',
            'fa' => 'Persian',
            'fi' => 'Finnish',
            'fj' => 'Fiji',
            'fo' => 'Faeroese',
            'fr' => 'French',
            'fy' => 'Frisian',
            'ga' => 'Irish',
            'gd' => 'Scots/Gaelic',
            'gl' => 'Galician',
            'gn' => 'Guarani',
            'gu' => 'Gujarati',
            'ha' => 'Hausa',
            'hi' => 'Hindi',
            'hr' => 'Croatian',
            'hu' => 'Hungarian',
            'hy' => 'Armenian',
            'ia' => 'Interlingua',
            'ie' => 'Interlingue',
            'ik' => 'Inupiak',
            'in' => 'Indonesian',
            'is' => 'Icelandic',
            'it' => 'Italian',
            'iw' => 'Hebrew',
            'ja' => 'Japanese',
            'ji' => 'Yiddish',
            'jw' => 'Javanese',
            'ka' => 'Georgian',
            'kk' => 'Kazakh',
            'kl' => 'Greenlandic',
            'km' => 'Cambodian',
            'kn' => 'Kannada',
            'ko' => 'Korean',
            'ks' => 'Kashmiri',
            'ku' => 'Kurdish',
            'ky' => 'Kirghiz',
            'la' => 'Latin',
            'ln' => 'Lingala',
            'lo' => 'Laothian',
            'lt' => 'Lithuanian',
            'lv' => 'Latvian/Lettish',
            'mg' => 'Malagasy',
            'mi' => 'Maori',
            'mk' => 'Macedonian',
            'ml' => 'Malayalam',
            'mn' => 'Mongolian',
            'mo' => 'Moldavian',
            'mr' => 'Marathi',
            'ms' => 'Malay',
            'mt' => 'Maltese',
            'my' => 'Burmese',
            'na' => 'Nauru',
            'ne' => 'Nepali',
            'nl' => 'Dutch',
            'no' => 'Norwegian',
            'oc' => 'Occitan',
            'om' => '(Afan)/Oromoor/Oriya',
            'pa' => 'Punjabi',
            'pl' => 'Polish',
            'ps' => 'Pashto/Pushto',
            'pt' => 'Portuguese',
            'qu' => 'Quechua',
            'rm' => 'Rhaeto-Romance',
            'rn' => 'Kirundi',
            'ro' => 'Romanian',
            'ru' => 'Russian',
            'rw' => 'Kinyarwanda',
            'sa' => 'Sanskrit',
            'sd' => 'Sindhi',
            'sg' => 'Sangro',
            'sh' => 'Serbo-Croatian',
            'si' => 'Singhalese',
            'sk' => 'Slovak',
            'sl' => 'Slovenian',
            'sm' => 'Samoan',
            'sn' => 'Shona',
            'so' => 'Somali',
            'sq' => 'Albanian',
            'sr' => 'Serbian',
            'ss' => 'Siswati',
            'st' => 'Sesotho',
            'su' => 'Sundanese',
            'sv' => 'Swedish',
            'sw' => 'Swahili',
            'ta' => 'Tamil',
            'te' => 'Tegulu',
            'tg' => 'Tajik',
            'th' => 'Thai',
            'ti' => 'Tigrinya',
            'tk' => 'Turkmen',
            'tl' => 'Tagalog',
            'tn' => 'Setswana',
            'to' => 'Tonga',
            'tr' => 'Turkish',
            'ts' => 'Tsonga',
            'tt' => 'Tatar',
            'tw' => 'Twi',
            'uk' => 'Ukrainian',
            'ur' => 'Urdu',
            'uz' => 'Uzbek',
            'vi' => 'Vietnamese',
            'vo' => 'Volapuk',
            'wo' => 'Wolof',
            'xh' => 'Xhosa',
            'yo' => 'Yoruba',
            'zh' => 'Chinese',
            'zu' => 'Zulu',
        );

        $languages_zh = array(
            'en' => '英语',
            'aa' => '远',
            'ab' => '阿布哈兹',
            'af' => '南非荷兰语',
            'am' => '阿姆哈拉语',
            'ar' => '阿拉伯',
            'as' => '阿萨姆',
            'ay' => '艾马拉',
            'az' => '阿塞拜疆',
            'ba' => '巴什基尔',
            'be' => '白俄罗斯',
            'bg' => '保加利亚语',
            'bh' => '比哈里',
            'bi' => '比斯拉马语',
            'bn' => '孟加拉/孟加拉语',
            'bo' => '藏',
            'br' => '布列塔尼',
            'ca' => '加泰罗尼亚',
            'co' => '科西嘉',
            'cs' => '捷克',
            'cy' => '威尔士语',
            'da' => '丹麦',
            'de' => '德语',
            'dz' => '不丹',
            'el' => '希腊语',
            'eo' => '世界语',
            'es' => '西班牙语',
            'et' => '爱沙尼亚语',
            'eu' => '巴斯克',
            'fa' => '波斯语',
            'fi' => '芬兰',
            'fj' => '斐',
            'fo' => '法罗群岛',
            'fr' => '法国',
            'fy' => '弗里斯兰',
            'ga' => '爱尔兰的',
            'gd' => '苏格兰/盖尔',
            'gl' => '加利西亚',
            'gn' => '瓜拉尼',
            'gu' => '古吉拉特语',
            'ha' => '豪萨语',
            'hi' => '印地语',
            'hr' => '克罗地亚',
            'hu' => '匈牙利',
            'hy' => '亚美尼亚',
            'ia' => '国际语',
            'ie' => '国际语',
            'ik' => '伊努必语',
            'in' => '印度尼西亚',
            'is' => '冰岛的',
            'it' => '意大利',
            'iw' => '希伯来语',
            'ja' => '日本',
            'ji' => '意第绪语',
            'jw' => '爪哇',
            'ka' => '格鲁吉亚',
            'kk' => '哈萨克人',
            'kl' => '格陵兰',
            'km' => '柬埔寨',
            'kn' => '卡纳达语',
            'ko' => '朝鲜的',
            'ks' => '克什米尔',
            'ku' => '库尔德',
            'ky' => '吉尔吉斯人',
            'la' => '拉丁',
            'ln' => '林加拉语',
            'lo' => '老挝语',
            'lt' => '立陶宛',
            'lv' => '拉脱维亚语/列托语',
            'mg' => '马尔加什',
            'mi' => '毛利',
            'mk' => '马其顿',
            'ml' => '马拉雅拉姆语',
            'mn' => '蒙',
            'mo' => '摩尔多瓦',
            'mr' => '马拉',
            'ms' => '马来语',
            'mt' => '马耳他语',
            'my' => '缅甸语',
            'na' => '瑙鲁',
            'ne' => '尼泊尔',
            'nl' => '荷兰人',
            'no' => '挪威',
            'oc' => '奥克',
            'om' => '（阿凡）/ Oromoor/奥里亚',
            'pa' => '旁遮普',
            'pl' => '抛光',
            'ps' => '普什图语/普什图语',
            'pt' => '葡萄牙语',
            'qu' => '克丘亚语',
            'rm' => '里托罗曼',
            'rn' => '基隆迪',
            'ro' => '罗马尼亚',
            'ru' => '俄语',
            'rw' => '卢旺达语',
            'sa' => '梵文',
            'sd' => '信德',
            'sg' => '桑格罗',
            'sh' => '塞尔维亚 - 克罗地亚语',
            'si' => '僧伽罗人',
            'sk' => '斯洛伐克',
            'sl' => '斯洛文尼亚',
            'sm' => '萨摩亚',
            'sn' => '绍纳语',
            'so' => '索马里',
            'sq' => '阿尔巴尼亚人',
            'sr' => '塞尔维亚',
            'ss' => '斯瓦蒂语',
            'st' => '塞索托语',
            'su' => '巽',
            'sv' => '瑞典',
            'sw' => '斯瓦希里',
            'ta' => '泰米尔人',
            'te' => '泰卢固语',
            'tg' => '塔吉克',
            'th' => '泰国',
            'ti' => '提格雷语',
            'tk' => '土库曼',
            'tl' => '他加禄语',
            'tn' => '茨瓦纳语',
            'to' => '汤加',
            'tr' => '土耳其',
            'ts' => '特松加',
            'tt' => '鞑靼',
            'tw' => 'Twi',
            'uk' => '乌克兰',
            'ur' => '乌尔都语',
            'uz' => '乌兹别克',
            'vi' => '越南',
            'vo' => '沃拉普克语',
            'wo' => '沃洛夫语',
            'xh' => '科萨',
            'yo' => '约鲁巴',
            'zh' => '中文',
            'zu' => '祖鲁',
        );

        $languages_es = array(
            'en' => 'Inglés',
            'aa' => 'Lejos',
            'ab' => 'Abjasio',
            'af' => 'Afrikaans',
            'am' => 'Amárico',
            'ar' => 'Arábica',
            'as' => 'Asamés',
            'ay' => 'Aimara',
            'az' => 'Azerbaiyana',
            'ba' => 'Bashkir',
            'be' => 'Bielorrusa',
            'bg' => 'Búlgaro',
            'bh' => 'Bihari',
            'bi' => 'Bislama',
            'bn' => 'Bengalí / Bangla',
            'bo' => 'Tibetano',
            'br' => 'Bretón',
            'ca' => 'Catalana',
            'co' => 'Crso',
            'cs' => 'Checo',
            'cy' => 'Galés',
            'da' => 'Danés',
            'de' => 'Aleman',
            'dz' => 'Butaní',
            'el' => 'Griega',
            'eo' => 'Esperanto',
            'es' => 'Español',
            'et' => 'Estonio',
            'eu' => 'Vasco',
            'fa' => 'Persa',
            'fi' => 'Finlandés',
            'fj' => 'Fiyi',
            'fo' => 'Faeroese',
            'fr' => 'Francés',
            'fy' => 'Frisio',
            'ga' => 'Irlandés',
            'gd' => 'Escoceses / gaélico',
            'gl' => 'Gallego',
            'gn' => 'Guaraní',
            'gu' => 'Gujarati',
            'ha' => 'Hausa',
            'hi' => 'Hindi',
            'hr' => 'Croata',
            'hu' => 'Húngaro',
            'hy' => 'Armenio',
            'ia' => 'Interlingua',
            'ie' => 'Interlingue',
            'ik' => 'Inupiak',
            'in' => 'Indonesio',
            'is' => 'Islandés',
            'it' => 'Italiano',
            'iw' => 'Hebreo',
            'ja' => 'Japonés',
            'ji' => 'Yídish',
            'jw' => 'Javanés',
            'ka' => 'Georgiano',
            'kk' => 'Kazajo',
            'kl' => 'Groenlandés',
            'km' => 'Camboyano',
            'kn' => 'Kannada',
            'ko' => 'Coreano',
            'ks' => 'Kashmiri',
            'ku' => 'Kurdo',
            'ky' => 'Kirghiz',
            'la' => 'Latín',
            'ln' => 'Lingala',
            'lo' => 'Laothiano',
            'lt' => 'Lituano',
            'lv' => 'Letón / Lettish',
            'mg' => 'Madagascarí',
            'mi' => 'Maorí',
            'mk' => 'Macedónio',
            'ml' => 'Malayalam',
            'mn' => 'Mongol',
            'mo' => 'Moldavo',
            'mr' => 'Marathi',
            'ms' => 'Malayo',
            'mt' => 'Maltés',
            'my' => 'Birmano',
            'na' => 'Nauru',
            'ne' => 'Nepalí',
            'nl' => 'Holandés',
            'no' => 'Noruego',
            'oc' => 'Occitano',
            'om' => '(Afan)/Oromoor/Oriya',
            'pa' => 'Punjabi',
            'pl' => 'Polaco',
            'ps' => 'Pashto/Pushto',
            'pt' => 'Portugués',
            'qu' => 'Quechua',
            'rm' => 'Rhaeto-Romance',
            'rn' => 'Kirundi',
            'ro' => 'Rumano',
            'ru' => 'Ruso',
            'rw' => 'Kinyarwanda',
            'sa' => 'Sánscrito',
            'sd' => 'Sindhi',
            'sg' => 'Sangro',
            'sh' => 'Serbocroata',
            'si' => 'Cingalés',
            'sk' => 'Eslovaco',
            'sl' => 'Esloveno',
            'sm' => 'Samoano',
            'sn' => 'Shona',
            'so' => 'Somalí',
            'sq' => 'Albanés',
            'sr' => 'Serbio',
            'ss' => 'Siswati',
            'st' => 'Sesotho',
            'su' => 'Sundanés',
            'sv' => 'Sueco',
            'sw' => 'Swahili',
            'ta' => 'Tamil',
            'te' => 'Tegulu',
            'tg' => 'Tayiko',
            'th' => 'Tailandés',
            'ti' => 'Tigrinya',
            'tk' => 'Turcomanos',
            'tl' => 'Tagalo',
            'tn' => 'Setswana',
            'to' => 'Tonga',
            'tr' => 'Turco',
            'ts' => 'Tsonga',
            'tt' => 'Tártaro',
            'tw' => 'Twi',
            'uk' => 'Ucranio',
            'ur' => 'Urdu',
            'uz' => 'Uzbeko',
            'vi' => 'Vietnamita',
            'vo' => 'Volapuk',
            'wo' => 'Wolof',
            'xh' => 'Xhosa',
            'yo' => 'Yoruba',
            'zh' => 'Chino',
            'zu' => 'Zulú',
        );

        $languages_fr = array(
            'en' => 'Anglais',
            'aa' => 'Afar',
            'ab' => 'Abkhaze',
            'af' => 'Afrikaans',
            'am' => 'Amharique',
            'ar' => 'Arabe',
            'as' => 'Assamais',
            'ay' => 'Aymara',
            'az' => 'Azerbaïdjanais',
            'ba' => 'Bachkir',
            'be' => 'Biélorusse',
            'bg' => 'Bulgare',
            'bh' => 'Bihari',
            'bi' => 'Bislama',
            'bn' => 'Bengali / Bangla',
            'bo' => 'Tibétain',
            'br' => 'Breton',
            'ca' => 'Catalan',
            'co' => 'Corse',
            'cs' => 'Tchèque',
            'cy' => 'Gallois',
            'da' => 'Danois',
            'de' => 'Allemand',
            'dz' => 'Bhutani',
            'el' => 'Grec',
            'eo' => 'Espéranto',
            'es' => 'Espanol',
            'et' => 'Estonien',
            'eu' => 'Basque',
            'fa' => 'Persan',
            'fi' => 'Finlandais',
            'fj' => 'Fidji',
            'fo' => 'Féroé',
            'fr' => 'Français',
            'fy' => 'Frison',
            'ga' => 'Irlandais',
            'gd' => 'Écossais / gaélique',
            'gl' => 'Galicien',
            'gn' => 'Guarani',
            'gu' => 'Gujarati',
            'ha' => 'Hausa',
            'hi' => 'Hindi',
            'hr' => 'Croate',
            'hu' => 'Hongrois',
            'hy' => 'Arménien',
            'ia' => 'Interlingua',
            'ie' => 'Interlingue',
            'ik' => 'Inupiak',
            'in' => 'Indonésien',
            'is' => 'Islandais',
            'it' => 'Italien',
            'iw' => 'Hébreu',
            'ja' => 'Japonais',
            'ji' => 'Yiddish',
            'jw' => 'Javanais',
            'ka' => 'Géorgien',
            'kk' => 'Kazakh',
            'kl' => 'Groenlandais',
            'km' => 'Cambodgien',
            'kn' => 'Kannada',
            'ko' => 'Coréen',
            'ks' => 'Kashmiri',
            'ku' => 'Kurde',
            'ky' => 'Kirghiz',
            'la' => 'Latin',
            'ln' => 'Lingala',
            'lo' => 'Laothien',
            'lt' => 'Lituanien',
            'lv' => 'Letton',
            'mg' => 'Malgache',
            'mi' => 'Maori',
            'mk' => 'Macédonien',
            'ml' => 'Malayalam',
            'mn' => 'Mongol',
            'mo' => 'Moldave',
            'mr' => 'Marathi',
            'ms' => 'Malais',
            'mt' => 'Maltais',
            'my' => 'Birman',
            'na' => 'Nauru',
            'ne' => 'Népalais',
            'nl' => 'Néerlandais',
            'no' => 'Norvégien',
            'oc' => 'Occitan',
            'om' => '(Afan)/Oromoor/Oriya',
            'pa' => 'Punjabi',
            'pl' => 'Polonais',
            'ps' => 'Pashto/Pushto',
            'pt' => 'Portugais',
            'qu' => 'Quechua',
            'rm' => 'Rhéto-Romance',
            'rn' => 'Kirundi',
            'ro' => 'Roumain',
            'ru' => 'Russe',
            'rw' => 'Kinyarwanda',
            'sa' => 'Sanskrit',
            'sd' => 'Sindhi',
            'sg' => 'Sangro',
            'sh' => 'Serbo-croate',
            'si' => 'Cingalais',
            'sk' => 'Slovaque',
            'sl' => 'Slovène',
            'sm' => 'Samoan',
            'sn' => 'Shona',
            'so' => 'Somali',
            'sq' => 'Albanais',
            'sr' => 'Serbe',
            'ss' => 'Siswati',
            'st' => 'Sesotho',
            'su' => 'Sundanais',
            'sv' => 'Suédois',
            'sw' => 'Swahili',
            'ta' => 'Tamil',
            'te' => 'Télougou',
            'tg' => 'Tadjik',
            'th' => 'Thaïlandais',
            'ti' => 'Tigrinya',
            'tk' => 'Turkmène',
            'tl' => 'Tagalog',
            'tn' => 'Setswana',
            'to' => 'Tonga',
            'tr' => 'Turc',
            'ts' => 'Tsonga',
            'tt' => 'Tatar',
            'tw' => 'Twi',
            'uk' => 'Ukrainien',
            'ur' => 'Ourdou',
            'uz' => 'Ouzbek',
            'vi' => 'Vietnamien',
            'vo' => 'Volapuk',
            'wo' => 'Wolof',
            'xh' => 'Xhosa',
            'yo' => 'Yoruba',
            'zh' => 'Chinois',
            'zu' => 'Zoulou',
        );

        $languages_ar = array(
            'en' => 'الإنجليزية',
            'aa' => 'عفار',
            'ab' => 'الأبخازية',
            'af' => 'الأفريكانية',
            'am' => 'الأمهرية',
            'ar' => 'العربية',
            'as' => 'الأسامية',
            'ay' => 'الأيمارا',
            'az' => 'الأذربيجانية',
            'ba' => 'الباشكيرية',
            'be' => 'البيلاروسية',
            'bg' => 'البلغارية',
            'bh' => 'بيهاري',
            'bi' => 'البيسلامية',
            'bn' => 'البنغالية',
            'bo' => 'التبت',
            'br' => 'البريتونية',
            'ca' => 'الكاتالونية',
            'co' => 'الكورسيكية',
            'cs' => 'التشيكية',
            'cy' => 'الويلزية',
            'da' => 'الدنماركية',
            'de' => 'الالمانية',
            'dz' => 'بوتاني',
            'el' => 'اليونانية',
            'eo' => 'اسبرانتو',
            'es' => 'الاسبانية',
            'et' => 'الاستونية',
            'eu' => 'الباسك',
            'fa' => 'الفارسية',
            'fi' => 'الفنلندية',
            'fj' => 'فيجي',
            'fo' => 'فاروية',
            'fr' => 'الفرنسية',
            'fy' => 'الفريزية',
            'ga' => 'الايرلندية',
            'gd' => 'الاسكتلندية',
            'gl' => 'الجاليكية',
            'gn' => 'الجواراني',
            'gu' => 'الغوجاراتية',
            'ha' => 'الهوسا',
            'hi' => 'الهندية',
            'hr' => 'الكرواتية',
            'hu' => 'الهنغارية',
            'hy' => 'الأرمنية',
            'ia' => 'الإنترلنغوا',
            'ie' => 'الإنترلنغوا',
            'ik' => 'إنوبياك',
            'in' => 'الاندونيسية',
            'is' => 'الأيسلندية',
            'it' => 'الايطالية',
            'iw' => 'العبرية',
            'ja' => 'اليابانية',
            'ji' => 'الييدية',
            'jw' => 'الجاوية',
            'ka' => 'الجورجية',
            'kk' => 'الكازاخستانية',
            'kl' => 'جرينلاند',
            'km' => 'الكمبودية',
            'kn' => 'الكانادا',
            'ko' => 'الكورية',
            'ks' => 'الكشميرية',
            'ku' => 'الكردية',
            'ky' => 'القيرغيزية',
            'la' => 'اللاتينية',
            'ln' => 'لينجالا',
            'lo' => 'اللاوثية',
            'lt' => 'الليتوانية',
            'lv' => 'اللاتفية',
            'mg' => 'الملغاشية',
            'mi' => 'الماوري',
            'mk' => 'المقدونية',
            'ml' => 'المالايالامية',
            'mn' => 'المنغولية',
            'mo' => 'المولدوفا',
            'mr' => 'الماراثية',
            'ms' => 'الملايو',
            'mt' => 'المالطية',
            'my' => 'البورمية',
            'na' => 'الناورو',
            'ne' => 'النيبالية',
            'nl' => 'الهولندية',
            'no' => 'النرويجية',
            'oc' => 'الأوكيتانية',
            'om' => 'افان',
            'pa' => 'البنجابية',
            'pl' => 'البولندية',
            'ps' => 'البشتو',
            'pt' => 'البرتغالية',
            'qu' => 'الكيشوا',
            'rm' => 'راتو رمانس',
            'rn' => 'الكيروندي',
            'ro' => 'الرومانية',
            'ru' => 'الروسية',
            'rw' => 'الكينيارواندا',
            'sa' => 'السنسكريتية',
            'sd' => 'السندية',
            'sg' => 'السانجرو',
            'sh' => 'الصربية الكرواتية',
            'si' => 'السنهالية',
            'sk' => 'السلوفاكية',
            'sl' => 'السلوفينية',
            'sm' => 'الساموا',
            'sn' => 'الشونا',
            'so' => 'الصومالية',
            'sq' => 'الألبانية',
            'sr' => 'الصربية',
            'ss' => 'السيسواتي',
            'st' => 'السيسوتو',
            'su' => 'السودانية',
            'sv' => 'السويدية',
            'sw' => 'السواحيلية',
            'ta' => 'التاميل',
            'te' => 'التيلجو',
            'tg' => 'الطاجيكية',
            'th' => 'التايلاندية',
            'ti' => 'التغرينية',
            'tk' => 'التركمانية',
            'tl' => 'التاغالوغية',
            'tn' => 'الستسوانا',
            'to' => 'التونجا',
            'tr' => 'التركية',
            'ts' => 'التسونجا',
            'tt' => 'التتار',
            'tw' => 'التوي',
            'uk' => 'الأوكرانية',
            'ur' => 'الأوردية',
            'uz' => 'الأوزبكية',
            'vi' => 'الفيتنامية',
            'vo' => 'الفولابوك',
            'wo' => 'الولوف',
            'xh' => 'الخوزا',
            'yo' => 'اليوروبا',
            'zh' => 'الصينية',
            'zu' => 'الزولو',
        );

        foreach ($languages_en as $code => $languagename) {
            $language = new Language();
            $language->setCode($code);

            $language->translate('en')->setName($languagename);
            $language->translate('zh')->setName($languages_zh[$code]);
            $language->translate('es')->setName($languages_es[$code]);
            $language->translate('fr')->setName($languages_fr[$code]);
            $language->translate('ar')->setName($languages_ar[$code]);
            $language->mergeNewTranslations();
            $manager->persist($language);
        }

        $manager->flush();
    }

}
