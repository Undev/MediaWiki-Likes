<?php
/**
 * Date: 12.08.13
 * Time: 13:25
 */

if (!defined('MEDIAWIKI')) {
    die("This is not a valid entry point.\n");
}

$wgExtensionFunctions[] = 'wfSetupLikes';
$wgExtensionCredits['other'][] = array(
    'path' => __FILE__,
    'name' => 'Likes',
    'author' => '[http://www.facebook.com/denisovdenis Denisov Denis]',
    'url' => 'https://github.com/Undev/MediaWiki-Likes',
    'description' => 'Native Like-button on every page.',
    'version' => 0.1,
);
$wgExtensionMessagesFiles[] = dirname(__FILE__) . '/Likes.i18n.php';

/*
 * Autoload AjaxLogin API interface
 */
$dir = dirname(__FILE__) . '/';
$wgAutoloadClasses['ApiLikes'] = $dir . 'ApiLikes.php';
$wgAPIModules['likes'] = 'ApiLikes';

/**
 * Register ResourceLoader modules
 */
$commonModuleInfo = array(
    'localBasePath' => dirname(__FILE__) . '/modules',
    'remoteExtPath' => 'Likes/modules',
);

$wgResourceModules['ext.Likes'] = array(
        'scripts' => 'ext.Likes.js',
    ) + $commonModuleInfo;

$wgResourceModules['ext.Likes.css'] = array(
        'styles' => 'ext.Likes.css',
    ) + $commonModuleInfo;

class Likes
{
    /**
     * @var WikiPage
     */
    private $page;

    /**
     * @var User
     */
    private $user;

    /**
     * @var boolean Liked page or not
     */
    public $isLiked = false;

    /**
     * @var int Count of Likes
     */
    private $likes;

    public function __construct()
    {
        global $wgHooks;

        $wgHooks['OutputPageBeforeHTML'][] = $this;
        $wgHooks['ResourceLoaderGetConfigVars'][] = $this;
    }

    public function __toString()
    {
        return __CLASS__;
    }

    private function tableName()
    {
        return 'likes';
    }

    public function init()
    {
        try {
            $this->setHeaders(RequestContext::getMain()->getOutput());
            $this->page = RequestContext::getMain()->getWikiPage();
            $this->user = RequestContext::getMain()->getUser();
        } catch (Exception $e) {
            throw new Exception(__CLASS__ . wfMessage('likes-error-extension')->inContentLanguage()->plain());
        }

        return true;
    }

    public function onOutputPageBeforeHTML(OutputPage &$out, &$text)
    {
        try {
            $this->init();
        } catch (Exception $e) {
            return false;
        }

        if ($out->isArticle()) {
            $count = $this->getLikes();
            $html = $this->getLikeButton($count, true);

            $out->addHTML($html);
        }

        return true;
    }

    /**
     * Setting up variables to pass it to Javascript
     * @param $vars
     * @return bool
     */
    public function onResourceLoaderGetConfigVars(&$vars)
    {
        try {
            $this->init();
        } catch (Exception $e) {
            return false;
        }

        // @todo Correctly not worked. Now $var passed through input-hidden.
        $vars['likesPageId'] = $this->page->getId();
        $vars['likesUserId'] = $this->user->getId();

        return true;
    }

    /**
     * Set the script tags in an OutputPage object
     * @param OutputPage $outputPage
     */
    public function setHeaders($outputPage)
    {
        # Add the modules
        $outputPage->addModuleStyles('ext.' . __CLASS__ . '.css');
        $outputPage->addModules('ext.' . __CLASS__);
    }

    private function getLikes()
    {
        if (is_null($this->likes)) {
            $dbr = wfGetDB(DB_SLAVE);
            $res = $dbr->select(
                $this->tableName(),
                array('page_id', 'user_id'),
                array('page_id ' => $this->page->getId()),
                __METHOD__
            );

            if (empty($res) or $res->numRows() == 0) {
                return 0;
            }

            foreach ($res as $row) {
                if ($row->user_id == $this->user->getId()) {
                    $this->isLiked = true;
                }

                $this->likes++;
            }
        }

        return $this->likes;
    }

    private function getLikeButton()
    {
        $title = $this->isLiked ?
            wfMessage(strtolower(__CLASS__) . '-button-title-remove')->inContentLanguage()->plain() :
            wfMessage(strtolower(__CLASS__) . '-button-title-add')->inContentLanguage()->plain();

        $image = "/extensions/Likes/resources/FB-ThumbsUp_29.png";
        $css = $this->isLiked ? 'class="reflection"' : '';

        return <<<HTML
            <div id="likes">
                <a href="#" title="$title"><img src="$image" $css></a>
                <span>{$this->getLikes()}</span>
                <input id="ext-Likes-pageId" type="hidden" value="{$this->page->getId()}">
                <input id="ext-Likes-userId" type="hidden" value="{$this->user->getId()}">
                <input id="ext-Likes-isLiked" type="hidden" value="{$this->isLiked}">
            </div>
HTML;
    }
}

function wfSetupLikes()
{
    global $wgLikes;

    $wgLikes = new Likes;
}
