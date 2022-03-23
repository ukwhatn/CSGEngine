<?php

class User
{
    public DiscordUser $discordUser;
    public int $userID;
    public int $permission;

    /**
     * @param DiscordUser $discordUser
     * @param int $userID
     * @param int $permission
     */
    public function __construct(DiscordUser $discordUser, int $userID, int $permission)
    {
        $this->discordUser = $discordUser;
        $this->userID = $userID;
        $this->permission = $permission;
    }
}

class Site
{
    public string $name;
    public string $description;
    public string $rootURI;

    /**
     * @param string $name
     * @param string $description
     * @param string $rootURI
     */
    public function __construct(string $name, string $description, string $rootURI)
    {
        $this->name = $name;
        $this->description = $description;
        $this->rootURI = $rootURI;
    }
}

class Page
{
    public Site $site;
    public int $id;
    public string $path;
    public int $permission;

    /**
     * @param Site $site
     * @param string $path
     * @param int $permission
     */
    public function __construct(Site $site, int $id, string $path, int $permission)
    {
        $this->site = $site;
        $this->id = $id;
        $this->path = $path;
        $this->permission = $permission;
    }

}

class PageMetadata
{
    public Page $page;
    public int $id;
    public int $createdByID;
    public datetime $createdAt;
    public string $title;

    public function __construct(Page $page, int $id, int $createdByID, string $createdAt, string $title)
    {
        $this->page = $page;
        $this->id = $id;
        $this->createdByID = $createdByID;
        $this->createdAt = DateTime::createFromFormat('Y-m-d H:i:s', $createdAt);
        $this->title = $title;
    }

    public function getCreatedTimeString(): string
    {
        return $this->createdAt->format('Y/m/d H:i:s');
    }
}

class PageElement
{
    public PageMetadata $metadata;
    public int $id;
    public string $elementID;
    public int $createdByID;
    public datetime $createdAt;
    public string $source;

    /**
     * @param PageMetadata $metadata
     * @param int $id
     * @param string $elementID
     * @param int $createdByID
     * @param string $createdAt
     * @param string $source
     */
    public function __construct(PageMetadata $metadata, int $id, string $elementID, int $createdByID, string $createdAt, string $source)
    {
        $this->metadata = $metadata;
        $this->id = $id;
        $this->elementID = $elementID;
        $this->createdByID = $createdByID;
        $this->createdAt = DateTime::createFromFormat('Y-m-d H:i:s', $createdAt);
        $this->source = $source;
    }

    public function getCreatedTimeString(): string
    {
        return $this->createdAt->format('Y/m/d H:i:s');
    }

    public function parseSource(DB $DB)
    {
        /* Source parse */
        $source = $this->source;
        if (preg_match_all("/\[\[include\s(.+?)\s(.+?)\]\]/u", $source, $includeMatches, PREG_SET_ORDER)) {
            foreach ($includeMatches as $num => $data) {
                $allString = $data[0];
                $path = $data[1];
                $elementID = $data[2];
                if ($elementID === "this"){
                    $elementID = $this->elementID;
                }
                $targetPage = $DB->getPageMasterData($this->metadata->page->site, $path);
                if ($targetPage === null) {
                    continue;
                }
                $targetMetadata = $DB->getPageMetadata($targetPage);
                if ($targetMetadata === null) {
                    continue;
                }
                $targetElement = $DB->getPageElement($targetMetadata, $elementID);
                if ($targetElement === null) {
                    continue;
                }
                $source = str_replace($allString, $targetElement->source, $source);
            }
        }
        $this->source = $source;
    }

}

class File
{
    public int $id;
    public string $name;
    public string $mimetype;
    public datetime $uploadedAt;

    /**
     * @param int $id
     * @param string $mimetype
     * @param string $uploadedAt
     */
    public function __construct(int $id, string $name, string $mimetype, string $uploadedAt)
    {
        $this->id = $id;
        $this->name = $name;
        $this->mimetype = $mimetype;
        $this->uploadedAt = DateTime::createFromFormat('Y-m-d H:i:s', $uploadedAt);
    }

    public function getUploadedTimeString(): string
    {
        return $this->uploadedAt->format('Y/m/d H:i:s');
    }

    public function getLink(): string
    {
        return "/file--deliver/" . $this->id;
    }


}

class OGP
{
    public string $siteName;
    public string $url;
    public string $type;
    public string $pageTitle;
    public ?string $pageDescription;
    public ?string $image;
    public string $twCardType;

    public function __construct(
        string $siteName,
        string $url,
        string $pageTitle,
        string $pageDescription = null,
        string $image = null,
        bool   $isArticle = False,
        bool   $hasTwLargeCard = False
    )
    {
        $this->siteName = $siteName;
        $this->url = $url;
        $this->pageTitle = $pageTitle;
        $this->pageDescription = $pageDescription;
        $this->image = $image;
        if ($isArticle) {
            $this->type = "article";
        } else {
            $this->type = "website";
        }
        if ($hasTwLargeCard) {
            $this->twCardType = "Summary with Large Image";
        } else {
            $this->twCardType = "Summary";
        }
    }


}