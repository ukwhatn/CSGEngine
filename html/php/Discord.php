<?php

class DiscordOAuth2Exception extends Exception
{
}

class DiscordTokenizeException extends DiscordOAuth2Exception
{
}

class DiscordOAuth2
{
    public int $oauth2ClientID;
    public string $oauth2ClientSecret;

    public string $authorizeURL = "https://discord.com/api/oauth2/authorize";
    public string $tokenizeURL = "https://discord.com/api/oauth2/token";
    public string $apiURLMe = "https://discord.com/api/users/@me";
    public string $mainURL;

    function __construct(string $clientID, string $clientSecret, string $mainURL)
    {
        $this->oauth2ClientID = $clientID;
        $this->oauth2ClientSecret = $clientSecret;
        $this->mainURL = $mainURL;
    }

    public function getLoginURL(string $scope): string
    {
        $requestParams = array(
            "client_id" => $this->oauth2ClientID,
            "redirect_uri" => $this->mainURL,
            "response_type" => "code",
            "scope" => $scope
        );
        return $this->authorizeURL . "?" . http_build_query($requestParams);
    }

    /**
     * @throws DiscordTokenizeException
     */
    public function tokenize(string $code): DiscordUser
    {
        $requestParams = array(
            "client_id" => $this->oauth2ClientID,
            "client_secret" => $this->oauth2ClientSecret,
            "redirect_uri" => $this->mainURL,
            "grant_type" => "authorization_code",
            "code" => $code
        );
        $curl = curl_init();
        $curlOptions = array(
            CURLOPT_URL => $this->tokenizeURL,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $requestParams,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true
        );
        curl_setopt_array($curl, $curlOptions);
        $response = curl_exec($curl);
        curl_close($curl);
        $tokens = json_decode($response, true);
        if (!array_key_exists("access_token", $tokens) || !array_key_exists("refresh_token", $tokens) || array_key_exists("error", $tokens)) {
            // tokenize失敗
            throw new DiscordTokenizeException();
        }
        // tokenize成功
        $token = $tokens["access_token"];
        $refreshToken = $tokens["refresh_token"];
        return new DiscordUser($this, $token, $refreshToken);
    }
}


class DiscordUser
{
    public string $userID;
    public string $userName;
    public string $userDiscriminator;
    private DiscordOAuth2 $oauth2Client;
    private string $accessToken;
    private string $refreshToken;
    private string $userAvatarHash;

    function __construct(DiscordOAuth2 $oauth2Client, $accessToken, $refreshToken)
    {
        $this->oauth2Client = $oauth2Client;
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
    }

    /**
     * @throws DiscordTokenizeException
     */
    public function refreshToken()
    {
        $requestParams = array(
            "client_id" => $this->oauth2Client->oauth2ClientID,
            "client_secret" => $this->oauth2Client->oauth2ClientSecret,
            "grant_type" => "refresh_token",
            "refresh_token" => $this->refreshToken
        );
        $curl = curl_init();
        $curlOptions = array(
            CURLOPT_URL => $this->oauth2Client->tokenizeURL,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $requestParams,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true
        );
        curl_setopt_array($curl, $curlOptions);
        $response = curl_exec($curl);
        curl_close($curl);
        $tokens = json_decode($response, true);
        var_dump($tokens);
        if (!array_key_exists("access_token", $tokens) || !array_key_exists("refresh_token", $tokens) || array_key_exists("error", $tokens)) {
            // tokenize失敗
            throw new DiscordTokenizeException();
        }
        // tokenize成功
        $this->accessToken = $tokens["access_token"];
        $this->refreshToken = $tokens["refresh_token"];
    }

    public function getMe()
    {
        $curl = curl_init();
        $curlOptions = array(
            CURLOPT_URL => $this->oauth2Client->apiURLMe,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $this->accessToken
            )
        );
        curl_setopt_array($curl, $curlOptions);
        $response = curl_exec($curl);
        curl_close($curl);
        $data = json_decode($response, true);
        $this->userID = $data["id"];
        $this->userName = $data["username"];
        $this->userDiscriminator = $data["discriminator"];
        $this->userAvatarHash = $data["avatar"];
    }
}