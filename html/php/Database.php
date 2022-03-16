<?php

require_once __DIR__ . "/../php/DataClass.php";

class DatabaseException extends Exception
{
}

/**
 * コンストラクタ内でエラーが発生した場合にthrow
 */
class InitialConnectException extends DatabaseException
{
}

class DB
{
    public readonly PDO $connection;

    /**
     * @throws InitialConnectException
     */
    function __construct(
        string $host = "db",
        string $username = "application",
        string $password = "applicationpassword",
        string $schema = "Master")
    {
        try {
            $this->connection = new PDO(
                "mysql:dbname=" . $schema . ";host=" . $host,
                $username, $password);
        } catch (PDOException $e) {
            throw new InitialConnectException($e);
        }
    }

    public function getRootURI(): string
    {
        $sql = "SELECT * FROM Master.master_config WHERE name = 'root_uri'";
        $row = $this->connection->query($sql)->fetch(PDO::FETCH_ASSOC);
        return $row["value"];
    }

    public function getDiscordOAuthData(): array
    {
        $sql = "SELECT * FROM Master.master_config WHERE name LIKE 'discord_oauth2_%'";
        $rows = $this->connection->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $return_array = [];
        foreach ($rows as $row) {
            $return_array[$row["name"]] = $row["value"];
        }
        return $return_array;
    }

    public function getSiteConfigs(): array
    {
        $sql = "SELECT * FROM Master.site_config";
        $rows = $this->connection->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $return_array = [];
        foreach ($rows as $row) {
            $return_array[$row["name"]] = $row["value"];
        }
        return $return_array;
    }

    public function createNewPage(string $path, int $permission)
    {
        $sql = "INSERT INTO Master.page_master (path, permission) VALUES (:path, :permission)";
        $prepare = $this->connection->prepare($sql);
        $prepare->bindValue(":path", $path);
        $prepare->bindValue(":permission", $permission);
        $prepare->execute();
    }

    public function getPageMasterData(Site $site, string $path): ?Page
    {
        $sql = "SELECT * FROM Master.page_master WHERE path = :path";
        $prepare = $this->connection->prepare($sql);
        $prepare->bindValue(":path", $path);
        $prepare->execute();
        $row = $prepare->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        return new Page($site, $row["id"], $row["path"], $row["permission"]);
    }

    public function createNewMetadata(Page $page, User $user, string $title)
    {
        $sql = "INSERT INTO Master.page_metadata (page_id, created_by_id, title) VALUES (:pageID, :createdByID, :title)";
        $prepare = $this->connection->prepare($sql);
        $prepare->bindValue(":pageID", $page->id);
        $prepare->bindValue(":createdByID", $user->userID);
        $prepare->bindValue(":title", $title);
        $prepare->execute();
    }

    public function getPageMetadata(Page $page): ?PageMetadata
    {
        $sql = "SELECT * FROM Master.page_metadata WHERE page_id = :page_id ORDER BY id DESC LIMIT 1";
        $prepare = $this->connection->prepare($sql);
        $prepare->bindValue(":page_id", $page->id);
        $prepare->execute();
        $row = $prepare->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        return new PageMetadata($page, $row["id"], $row["created_by_id"], $row["created_at"], $row["title"]);
    }

    public function createNewPageElement(PageMetadata $metadata, User $user, string $element_id, string $source)
    {
        $sql = "INSERT INTO Master.page_element (page_id, metadata_id, element_id, created_by_id, source) VALUES (:pageID, :metadataID, :elementID, :createdByID, :source)";
        $prepare = $this->connection->prepare($sql);
        $prepare->bindValue(":pageID", $metadata->page->id);
        $prepare->bindValue(":metadataID", $metadata->id);
        $prepare->bindValue(":elementID", $element_id);
        $prepare->bindValue(":createdByID", $user->userID);
        $prepare->bindValue(":source", $source);
        $prepare->execute();
    }

    public function getPageElements(PageMetadata $metadata): ?array
    {
        $sql = "SELECT * FROM Master.page_element WHERE page_id = :page_id AND metadata_id = :metadata_id";
        $prepare = $this->connection->prepare($sql);
        $prepare->bindValue(":page_id", $metadata->page->id);
        $prepare->bindValue(":metadata_id", $metadata->id);
        $prepare->execute();
        $rows = $prepare->fetchAll(PDO::FETCH_ASSOC);
        if (count($rows) === 0) {
            return null;
        }
        $result = [];
        foreach ($rows as $row) {
            $result[$row["element_id"]] = new PageElement(
                $metadata, $row["id"], $row["element_id"], $row["created_by_id"], $row["created_at"], $row["source"]);
        }
        return $result;
    }

    public function getFiles(): ?array
    {
        $sql = "SELECT * FROM Master.file_master WHERE is_deleted = 0";
        $prepare = $this->connection->prepare($sql);
        $prepare->execute();
        $rows = $prepare->fetchAll(PDO::FETCH_ASSOC);
        if (count($rows) === 0) {
            return null;
        }
        $result = [];
        foreach ($rows as $row) {
            $result[] = new File($row["id"], $row["name"], $row["mimetype"], $row["uploaded_at"]);
        }
        return $result;
    }

    public function getFileData($fileID): ?array
    {
        $sql = "SELECT * FROM Master.file_master WHERE id = :id AND is_deleted = 0";
        $prepare = $this->connection->prepare($sql);
        $prepare->bindValue(":id", $fileID);
        $prepare->execute();
        $row = $prepare->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        return $row;
    }

    public function uploadFile($tmpName, $newName)
    {
        # get mime type
        $fInfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $fInfo->file($tmpName);
        # get bin
        $fileBin = file_get_contents($tmpName);

        $sql = "INSERT INTO Master.file_master (mimetype, name, content) VALUES (:mimetype, :new_name, :content)";
        $prepare = $this->connection->prepare($sql);
        $prepare->bindValue(":mimetype", $mimeType);
        $prepare->bindValue(":new_name", $newName);
        $prepare->bindValue(":content", $fileBin);
        $prepare->execute();
    }

    public function getUserData(DiscordUser $discordUser): ?User
    {
        $sql = "SELECT * FROM Master.user_master WHERE discord_user_id = :discordUserID";
        $prepare = $this->connection->prepare($sql);
        $prepare->bindValue(":discordUserID", $discordUser->userID);
        $prepare->execute();
        $row = $prepare->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        return new User(
            $discordUser,
            $row["id"],
            $row["permission"]
        );
    }

    public function updateDiscordUserName(DiscordUser $user)
    {
        $sql = "UPDATE Master.user_master SET discord_user_name = :userName, discord_user_discriminator = :discriminator WHERE discord_user_id = :id";
        $prepare = $this->connection->prepare($sql);
        $prepare->bindValue(":id", $user->userID);
        $prepare->bindValue(":userName", $user->userName);
        $prepare->bindValue(":discriminator", $user->userDiscriminator);
        $prepare->execute();
    }
}