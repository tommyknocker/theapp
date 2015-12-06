<?php
/**
 * l18n support class
 *
 * @author Olivier PEREZ <https://github.com/olivierperez/o80-i18n>
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
namespace App\Core;

use App;

class I18n
{

    /**
     * All available local code sets
     * @var array 
     */
    private $codeSets = [
        'af_ZA.UTF-8', 'sq_AL.UTF-8', 'ar_SA.UTF-8', 'eu_ES.UTF-8', 'be_BY.UTF-8', 'bs_BA.UTF-8', 'bg_BG.UTF-8',
        'ca_ES.UTF-8', 'hr_HR.UTF-8', 'zh_CN.UTF-8', 'zh_TW.UTF-8', 'cs_CZ.UTF-8', 'da_DK.UTF-8', 'nl_NL.UTF-8',
        'en.UTF-8', 'et_EE.UTF-8', 'fa_IR.UTF-8', 'ph_PH.UTF-8', 'fi_FI.UTF-8', 'fr_FR.UTF-8', 'fr_CH.UTF-8',
        'fr_BE.UTF-8', 'fr_CA.UTF-8', 'ga.UTF-8', 'gl_ES.UTF-8', 'ka_GE.UTF-8', 'de_DE.UTF-8', 'de_DE.UTF-8',
        'el_GR.UTF-8', 'gu.UTF-8', 'he_IL.utf8', 'hi_IN.UTF-8', 'hu.UTF-8', 'is_IS.UTF-8', 'id_ID.UTF-8',
        'it_IT.UTF-8', 'ja_JP.UTF-8', 'kn_IN.UTF-8', 'km_KH.UTF-8', 'ko_KR.UTF-8', 'lo_LA.UTF-8', 'lt_LT.UTF-8',
        'lat.UTF-8', 'ml_IN.UTF-8', 'ms_MY.UTF-8', 'mi_NZ.UTF-8', 'mi_NZ.UTF-8', 'mn.UTF-8', 'no_NO.UTF-8',
        'no_NO.UTF-8', 'nn_NO.UTF-8', 'pl.UTF-8', 'pt_PT.UTF-8', 'pt_BR.UTF-8', 'ro_RO.UTF-8', 'ru_RU.UTF-8',
        'mi_NZ.UTF-8', 'sr_CS.UTF-8', 'sk_SK.UTF-8', 'sl_SI.UTF-8', 'so_SO.UTF-8', 'es_ES.UTF-8', 'sv_SE.UTF-8',
        'tl.UTF-8', 'ta_IN.UTF-8', 'th_TH.UTF-8', 'mi_NZ.UTF-8', 'tr_TR.UTF-8', 'uk_UA.UTF-8', 'vi_VN.UTF-8'
    ];

    /**
     * The default language code
     * @var string 
     */
    private $defaultLanguage = null;

    /**
     * The lang dictionary
     * @var object 
     */
    private $dictionary = null;

    /**
     * Dictionary loaded flag
     * @var bool
     */
    private $isLoaded = null;

    /**
     * Current locale
     * @var string 
     */
    private $locale = null;

    /**
     * Array of pluralRules
     * @see https://developer.mozilla.org/en-US/docs/Mozilla/Localization/Localization_and_Plurals
     * @var array 
     */
    private $pluralRules = [];

    /**
     * Plural rule
     * @var int 
     */
    private $pluralRule = 1;

    public function __construct()
    {
        $this->pluralRules = [
            function ($number) { // Plural rule 0 (Chinese)
                return 0;
            },
            function ($number) { // Plural rule 1 (English)
                return $number == 1 ? 0 : 1;
            },
            function ($number) { // Plural rule 2 (French)
                return $number <= 1 ? 0 : 1;
            },
            function ($number) { // Plural rule 3 (Latvian)
                return $number == 0 ? 0 : ($number % 100 != 11 && $number % 10 == 1 ? 1 : 2);
            },
            function ($number) { // Plural rule 4 (Scottish Gaelic)
                return $number == 1 || $number == 11 ? 0 : ($number == 2 || $number == 12 ? 1 : (($number >= 3 && $number <= 10) || ($number >= 13 && $number <= 19) ? 2 : 3));
            },
            function ($number) { // Plural rule 5 (Romanian)
                return $number == 1 ? 0 : ($number == 0 || ($number < 20 && $number % 100 < 20) ? 1 : 2);
            },
            function ($number) { // Plural rule 6 (Lithuanian)
                return $number != 11 && $number % 10 == 1 ? 0 : ($number % 10 == 0 || ($number % 100 >= 11 && $number % 100 <= 19) ? 1 : 2);
            },
            function ($number) { // Plural rule 7 (Russian)
                return $number % 10 == 1 && $number != 11 ? 0 : (($number % 10 >= 2 && $number % 10 <= 4) && ($number != 12 && $number != 14) ? 1 : 2);
            },
            function ($number) { // Plural rule 8 (Slovak)
                return $number == 1 ? 0 : ($number >= 2 && $number <= 4 ? 1 : 2);
            },
            function ($number) { // Plural rule 9 (Polish)
                return $number == 1 ? 0 : (($number % 10 >= 2 && $number % 10 <= 4) && ($number != 12 && $number != 14) ? 1 : 2);
            },
            function ($number) { // Plural rule 10 (Slovenian)
                return $number % 100 == 1 ? 0 : ($number % 100 == 2 ? 1 : ($number % 100 == 3 || $number % 100 == 4 ? 2 : 3));
            },
            function ($number) { // Plural rule 11 (Irish Gaeilge)
                return $number == 1 ? 0 : ($number == 2 ? 1 : ($number >= 3 || $number <= 6 ? 2 : ($number >= 7 || $number <= 10 ? 3 : 4)));
            },
            function ($number) { // Plural rule 12 (Arabic)
                return $number == 1 ? 0 : ($number == 2 ? 1 : ($number % 100 >= 3 || $number % 100 <= 10 ? 2 : ($number != 0 && ($number % 100 > 2) ? 3 : ($number != 0 && $number % 100 <= 2 ? 4 : 5))));
            },
            function ($number) { // Plural rule 13 (Maltese)
                return $number == 1 ? 0 : ($number == 0 || ($number % 100 >= 1 && $number % 100 <= 10) ? 1 : ($number % 100 >= 11 && $number % 100 <= 19 ? 2 : 3));
            },
            function ($number) { // Plural rule 14 (Macedonian)
                return $number % 10 == 1 ? 0 : ($number % 10 == 2 ? 1 : 2);
            },
            function ($number) { // Plural rule 15 (Icelandic)
                return $number % 10 == 1 && $number != 11 ? 0 : 1;
            },
            function ($number) { // Plural rule 16 (Celtic)
                return $number == 1 ? 0 : ($number % 10 == 1 && !in_array($number, [11, 71, 91]) ? 1 : ($number % 10 == 2 && $number != 12 && $number != 72 && $number != 92 ? 2 : (in_array($number % 10, [3, 4, 9]) && !in_array($number, [13, 14, 19, 73, 73, 79, 93, 94, 99]) ? 3 : ($number > 1000000 && $number % 10 == 0 ? 4 : 5))));
            },
            ];
        }

    /**
     * Add language key to specified section
     * @param string $section
     * @param string $key
     */
    public function addKey($section, $key, $value)
    {

        if (!is_object($this->dictionary)) {
            $this->dictionary = new \stdClass();
        }

        if (!is_object($this->dictionary->$section)) {
            $this->dictionary->$section = new \stdClass();
        }

        $this->dictionary->$section->$key = $value;
    }

    /**
     * Determine languages, that can be used
     */
    private function determineLanguage()
    {
        $languages = [];

        if (App::Get()->get('lang', FILTER_SANITIZE_STRING)->result) {
            $languages[] = App::Get()->get('lang', FILTER_SANITIZE_STRING)->result;
        }

        if (isset($_SESSION['lang'])) {
            $languages[] = $_SESSION['lang'];
        }

        $languages = array_merge($languages, $this->getHttpAcceptLanguages());

        if (!empty($this->defaultLanguage)) {
            $languages[] = $this->defaultLanguage;
        }

        return $languages;
    }

    /**
     * Get the translation of the key, and format the result with args.
     * App::I18n->format('Section', 'Key', 'A value')
     *
     * @param string $section
     * @param string $key
     * @param array $args [optional]
     * @return string The formatted translation, or <code>[missing key:$key]</code> if not found
     */
    public function format($section, $key, $args = null)
    {
        $msg = $this->get($section, $key);
        return vsprintf($msg, $args);
    }

    /**
     * Get the translation of a key. The language will be automaticaly selected in :
     * $_GET, $_SESSION, $_SERVER or $defaultLanguage attribute.
     * <ul>
     *  <li>App::I18n()->get('Section', 'Some key')->result;</li>
     *  <li>App::I18n()->get('Generic', 'Yes')->result;</li>
     * </ul>
     *
     * @param string $section The section of the translation
     * @param string $key The key of the translation
     * @return string The translation, or <code>[missing key:$key]</code> if not found
     */
    public function get($section, $key)
    {
        return $this->getMessage($section, $key);
    }

    /**
     * Parse HTTP_ACCEPT_LANGUAGE and determine client supported languages
     * @return array
     */
    private function getHttpAcceptLanguages()
    {
        $result = array();

        preg_match_all("/([[:alpha:]]{1,8}(?:-[[:alpha:]|-]{1,8})?)" .
            "(?:\\s*;\\s*q\\s*=\\s*(?:1\\.0{0,3}|0\\.\\d{0,3}))?\\s*(?:,|$)/i", App::Get()->server('HTTP_ACCEPT_LANGUAGE')->result, $hits);

        foreach ($hits[1] as $hit) {
            $lang = str_replace('-', '_', $hit);
            $result[] = $lang;
        }

        return $result;
    }

    /**
     * Get current locale
     * @return string
     */
    public function getLocale()
    {
        if (!$this->isLoaded) {
            $this->load();
        }

        return $this->locale;
    }

    /**
     * Get message from dictionary
     * @param string $section
     * @param string $key
     * @return string
     */
    private function getMessage($section, $key)
    {
        if (!$this->isLoaded) {
            $this->load();
        }
        $message = $this->dictionary && isset($this->dictionary->$section) && isset($this->dictionary->$section->$key) ? $this->dictionary->$section->$key : null;

        if (!$message && App::Config()->i18n_autoadd) {
            $this->addKey($section, $key, $key);
            App::JSON()->write('i18n/' . $this->locale, $this->dictionary);
        }

        return $message ? : '[missing key: ' . $section . '.' . $key . ']';
    }

    /**
     * Get current plural rule number
     * @return int
     */
    public function getPluralRule()
    {
        return $this->pluralRule;
    }

    /**
     * List the files from the {@code path} directory and sort them by filename size desc.
     *
     * @return array Array of files found
     */
    public function listLangFiles()
    {
        $files = array_diff(scandir(DIR_DATA . 'i18n'), array('..', '.'));
        uasort($files, function ($a, $b) {
            return strlen($a) < strlen($b);
        });
        $files = array_filter($files, function($file) {

            return substr($file, -9) === '.json.php';
        });
        return $files;
    }

    /**
     * Load dictionary
     */
    public function load()
    {
        $languages = $this->determineLanguage();
        $files = $this->listLangFiles();

        foreach ($languages as $language) {
            list($dictionary, $locale) = $this->loadMatchingFile($files, $language);
            if ($dictionary !== null) {
                $this->locale = $locale;
                $this->dictionary = $dictionary;
                break;
            }
        }

        $this->isLoaded = true;

        if ($this->locale) {
            if (isset($this->dictionary->__configuration) && isset($this->dictionary->__configuration->pluralRule)) {
                try {
                    $this->setPluralRule($this->dictionary->__configuration->pluralRule);
                } catch (Exception $e) {
                    App::Log()->addWarning('No such plural rule {pluralRule}', ['pluraRule' => $this->dictionary->__configuration->pluralRule]);
                }
            }

            $this->setLocale();
        }
    }

    /**
     * Load the best dictionary looking at the language code given in parameter.
     *
     * @param array $files The array of dictionary file names
     * @param string $language The language code
     * @return array|null The dictionary found for the given language code, or null if there is no match.
     */
    private function loadMatchingFile($files, $language)
    {
        // Check all file names
        foreach ($files as $file) {
            // Extract locale from filename
            $fileNameParts = explode('.', $file);
            $fileLocale = $fileNameParts[0];
            if (\Locale::filterMatches($language, $fileLocale)) { // Check if filename matches $lang
                return [App::JSON()->read('i18n' . DS . $fileLocale)->result, $fileLocale];
            }
        }
        return null;
    }

    /**
     * Get the plural form of the translation of the key.
     * App::I18n->format('Section', 'Key', 'A value')
     *
     * @param string $section
     * @param string $key
     * @param int $number
     * @return string The correct plural form based on plural rule, or <code>[missing [key|plural]:$key]</code> if key/plural not found
     */
    public function plural($section, $key, $number)
    {
        $string = $this->getMessage($section, $key);
        $string = explode(';', $string);
        $pluralForm = $this->pluralRules[$this->getPluralRule()](abs((int) $number));

        return isset($string[$pluralForm]) ? $string[$pluralForm] : '[missing plural: ' . $section . '.' . $key . ']';
    }

    /**
     * Say to PHP the locale to use for loaded lang
     */
    private function setLocale()
    {
        foreach ($this->codeSets as $code) {
            if (substr($code, 0, strlen($this->locale)) === $this->locale) {
                setlocale(LC_TIME, $code);
                break;
            }
        }
    }

    /**
     * Set the default language.
     *
     * @param string $defaultLanguage The default language to use when the other doesn't match
     */
    public function setDefaultLanguage($defaultLanguage)
    {
        $this->defaultLanguage = $defaultLanguage;
    }

    /**
     * Set plural rule
     * @param int $rule
     * @throws \Exception
     */
    public function setPluralRule($rule)
    {
        if ($rule < 0 && $rule > count($this->pluralRules) - 1) {
            throw new \Exception('No such plural rule ' . $rule);
        }

        $this->pluralRule = $rule;
    }
}
