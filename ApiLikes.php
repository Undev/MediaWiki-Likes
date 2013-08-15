<?php
/**
 * Author: Denisov Denis
 * Email: denisovdenis@me.com
 * Date: 14.08.13
 * Time: 19:51
 */

if (!defined('MEDIAWIKI')) {
    die("This is not a valid entry point.\n");
}

class ApiLikes extends ApiBase
{
    protected function getAllowedParams()
    {
        return array(
            'pageId' => null,
            'userId' => null,
        );
    }

    private function tableName()
    {
        return 'likes';
    }

    public function execute()
    {
        wfSetupSession();
        $pageId = $userId = null;
        extract($this->extractRequestParams());

        if (!$pageId or !$userId) {
            $this->getResult()->addValue(null, 'error', 'Variables is not defined.');
        }

        $dbr = wfGetDB(DB_SLAVE);
        $res = $dbr->select(
            $this->tableName(),
            array('page_id', 'user_id'),
            array(
                'page_id ' => $pageId,
                'user_id ' => $userId,
            ),
            __METHOD__
        );

        $isLiked = $res->numRows() == 0 ? false : true ;
        $action = $isLiked ? 'delete' : 'insert';

        $res = $dbr->$action(
            $this->tableName(),
            array(
                'page_id ' => $pageId,
                'user_id ' => $userId,
            ),
            __METHOD__
        );

        $this->getResult()->addValue(null, 'success', 'Like has been saved');
    }
}