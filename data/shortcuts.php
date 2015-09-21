<?php
/**
 * Some needful shortcuts
 * 
 * @author Olivier PEREZ <https://github.com/olivierperez/o80-i18n>
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
use App\Core;

/**
 * This method is a shortcut to <code>App::Tr()-&gt;get(...)->result;</code>.
 *
 * Examples:
 * <ul>
 *  <li>__('Section', 'Key')</li>
 *  <li>__('Generic', 'Yes')</li>
 * </ul>
 *
 * @param string $section The Section of the translation
 * @param string $key The key of the translation
 * @return string The translation
 */
function __($section, $key)
{
    return App::I18n()->get($section, $key)->result;
}

/**
 * This method is a shortcut to <code>App::Tr()-&gt;format(...)->result;</code>.
 *
 * @param string $section The Section of the translation
 * @param string $key The key of the translation
 * @param mixed $args [optional]
 * @return string The formatted translation
 */
function __f($section, $key)
{
    $args = array_slice(func_get_args(), 2);
    return App::I18n()->format($section, $key, $args)->result;
}

/**
 * This method is a shortcut to <code>App::Tr()-&gt;plural(...)->result;</code>.
 *
 * @param string $section The Section of the translation
 * @param string $key The key of the translation
 * @param int $number The number used to determine plural form
 */
function __p($section, $key, $number)
{
    return App::I18n()->plural($section, $key, $number)->result;
}
