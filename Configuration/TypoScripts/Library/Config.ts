# ---------------------------- General settings ---------------------------- #
config {
    # Generate document type declaration and an XML prologue for HTML5:
    doctype = html5

    htmlTag_setParams = class="no-js"

    # If "none" then the default XML prologue is not set.
    xmlprologue = none

    # It might happen that TYPO3 generates links with the same parameter twice or more.
    # This is no problem because only the last parameter is used,
    # thus the problem is just a cosmetical one.
    uniqueLinkVars = 1

    # If set, the admin panel appears in the bottom of pages:
    admPanel = 0

    # If set, then all email addresses in typolinks will be encrypted so spam
    # bots cannot detect them.
    spamProtectEmailAddresses = ascii
    spamProtectEmailAddresses_atSubst = &#64;
    spamProtectEmailAddresses_lastDotSubst = &#46;

    # If set, the stdWrap property prefixComment will be disabled,
    # thus preventing any revealing and spaceconsuming comments in the HTML source code.
    disablePrefixComment  = 1

    disableImgBorderAttr = 1

    # With this setting the cache always expires at midnight of the day, the page is scheduled to expire.
    cache_clearAtMidnight = 1

    # CSS settings
    concatenateCss = 1
    compressCss = 1

    # JS settings
    concatenateJs = 0
    compressJs = 0

    # HTTP_GET_VARS, which should be passed on with links in TYPO3.
    # This is compiled into a string stored in $GLOBALS['TSFE']->linkVars. The values are rawurlencoded in PHP.
    linkVars = L(0,1)

    baseURL = {$website.baseURL}

    inlineStyle2TempFile = 0

    config.index_enable = 1

}

# ---------------------------- Language settings ---------------------------- #
config {
    # Setting various modes of handling localization:
    sys_language_mode = {$website.sys_language_mode}

    # This value points to the uid of a record from the sys_language table:
    sys_language_uid = {$website.sys_language_uid}

    # Language key. See stdWrap.lang for more information:
    language = {$website.language}

    # PHP: setlocale("LC_ALL", [value]);
    locale_all = {$website.locale_all}

    # Set the language value for the attributes "xml:lang" and "lang" in the <html> tag, default is en:
    htmlTag_langKey = {$website.htmlTag_langKey}

}

# ---------------------------- realurl settings ---------------------------- #
config {

    # If set to one of the keywords, the content will have all local anchors in links prefixed with the path of the script.
    # Basically this means that <a href="#"> will be transformed to <a href="path/path/script?params#">.
    # This procedure is necessary if the <base> tag is set in the script (eg. if "realurl" extension is used to produce Speaking URLs).
    prefixLocalAnchors = all

    # Enables RealURL:
    tx_realurl_enable = 1
}