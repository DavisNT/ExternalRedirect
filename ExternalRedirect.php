<?php
/* ExternalRedirect - MediaWiki extension to allow redirects to external sites.
 * Copyright (C) 2013 Davis Mosenkovs
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */

if(!defined('MEDIAWIKI'))
    die();

$wgExtensionCredits[ 'parserhook' ][] = array(
    'path' => __FILE__,
    'name' => 'ExternalRedirect',
    'author' => 'Davis Mosenkovs',
    'url' => 'https://www.mediawiki.org/wiki/Extension:ExternalRedirect',
    'description' => 'Allows to make redirects to external websites',
    'version' => '1.0.2',
);

$wgExtensionMessagesFiles['ExternalRedirect'] = dirname( __FILE__ ) . '/ExternalRedirect.i18n.php';

/*** Default configuration

// Populate this array with NUMERIC namespace IDs where external redirection should be allowed. 
// see http://www.mediawiki.org/wiki/Manual:Namespace#Built-in_namespaces
// This example allows redirects from help pages (12) and user pages (2).
$wgExternalRedirectNsIDs = array(12, 2);  

// Note: I don't recommend you use this method as it can be easily exploited.
// Populate this array with page names (see magic word {{FULLPAGENAME}}) where external redirection should be allowed.
// This example allows redirects from the wiki pages about Warren G. Harding and the Teapot Dome Scandal.
$wgExternalRedirectPages = array('Teapot_Dome_scandal', 'Warren_G._Harding'); 

// Whether to display link to redirection URL (along with error message) in case externalredirect is used where it is not allowed.
$wgExternalRedirectDeniedShowURL = false;

***************************/

$wgHooks['ParserFirstCallInit'][] = 'wfExternalRedirectParserInit';

function wfExternalRedirectParserInit( Parser $parser ) {
    $parser->setFunctionHook( 'externalredirect', 'wfExternalRedirectRender');
    return true;
}

function wfExternalRedirectRender($parser, $url = '') {
    global $wgExternalRedirectNsIDs, $wgExternalRedirectPages, $wgExternalRedirectDeniedShowURL;
    $parser->disableCache();
    if(!wfParseUrl($url) || strpos($url, chr(13))!==false || strpos($url, chr(10))!==false) {
        return wfMessage('externalredirect-invalidurl')->text();
    }
    if(in_array($parser->getTitle()->getNamespace(), $wgExternalRedirectNsIDs, true) 
      || in_array($parser->getTitle()->getPrefixedText(), $wgExternalRedirectPages, true)) {
        header('Location: '.$url);
        return wfMessage('externalredirect-text', $url)->text();
    } else {
        return wfMessage('externalredirect-denied')->text().($wgExternalRedirectDeniedShowURL 
          ? ' '.wfMessage('externalredirect-denied-url', $url)->text() : "");
    }
}
