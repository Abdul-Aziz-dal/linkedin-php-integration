<?php

class LinkedInClient
{
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $accessToken;

    public function __construct()
    {
        $config = require __DIR__ . '/../config.php';
        $this->clientId     = $config['client_id'];
        $this->clientSecret = $config['client_secret'];
        $this->redirectUri  = $config['redirect_uri'];

    }


    public function fetchAccessToken($code)
    {
      
        $tokenUrl = 'https://www.linkedin.com/oauth/v2/accessToken';

        $data = [
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => $this->redirectUri,
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
        ];

        try {
            $ch = curl_init($tokenUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

            $response = curl_exec($ch);
            curl_close($ch);

            $tokenData = json_decode($response, true);

            if (isset($tokenData['access_token'])) {
                $this->accessToken = $tokenData['access_token'];
                return $tokenData;
            }

            throw new Exception("Failed to fetch access token: " . ($tokenData['error_description'] ?? 'Unknown error'));
        } catch (Exception $e) {
            echo("LinkedIn Auth Error: " . $e->getMessage());
            return null;
        }
    }

    public function getData(string $url): array
    {
        $headers = [
            "Authorization: Bearer {$this->accessToken}"
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        curl_close($ch);

        if ($result === false) {
            throw new \Exception("Failed to fetch data from LinkedIn API.");
        }

        $data = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Failed to parse LinkedIn API response: " . json_last_error_msg());
        }

        return $data;
    }

    // public function shareOnLinkedIn($accessToken,$author_urn, $text, $image_url = null, $video_url = null) {
    //     try {
    //         $url = 'https://api.linkedin.com/v2/ugcPosts';
    
    //         // Base payload with text only
    //         $shareContent = [
    //             "shareCommentary" => ["text" => $text],
    //             "shareMediaCategory" => "NONE"
    //         ];
    
    //         // Add image if provided
    //         if ($image_url) {
    //             $shareContent = [
    //                 "shareCommentary" => ["text" => $text],
    //                 "shareMediaCategory" => "IMAGE",
    //                 "media" => [
    //                     [
    //                         "status" => "READY",
    //                         "description" => ["text" => $text],
    //                         "media" => $image_url,
    //                         "title" => ["text" => "Image"]
    //                     ]
    //                 ]
    //             ];
    //         }
    
    //         // Add video if provided (overrides image)
    //         if ($video_url) {
    //             $shareContent = [
    //                 "shareCommentary" => ["text" => $text],
    //                 "shareMediaCategory" => "VIDEO",
    //                 "media" => [
    //                     [
    //                         "status" => "READY",
    //                         "description" => ["text" => $text],
    //                         "media" => $video_url,
    //                         "title" => ["text" => "Video"]
    //                     ]
    //                 ]
    //             ];
    //         }
    
    //         $payload = [
    //             "author" => $author_urn,
    //             "lifecycleState" => "PUBLISHED",
    //             "specificContent" => [
    //                 "com.linkedin.ugc.ShareContent" => $shareContent
    //             ],
    //             "visibility" => [
    //                 "com.linkedin.ugc.MemberNetworkVisibility" => "PUBLIC"
    //             ]
    //         ];
    
    //         $headers = [
    //             "Authorization: Bearer $accessToken",
    //             "Content-Type: application/json",
    //             "X-Restli-Protocol-Version: 2.0.0"
    //         ];
    
    //         $ch = curl_init($url);
    //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //         curl_setopt($ch, CURLOPT_POST, true);
    //         curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    //         curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    //         $response = curl_exec($ch);
    //         $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //         curl_close($ch);
    
    //         return [$http_status, $response];
    //     } catch (\Exception $e) {
    //         return [500, json_encode(['error' => $e->getMessage()])];
    //     }
    // }

    // public function shareOnLinkedIn($accessToken, $author_urn, $text, $image_url = null, $video_url = null) {
    //     try {
    //         $mediaUrn = null;
    
    //         // STEP 1: Handle image upload if image URL is provided
    //         if ($image_url) {
    //             $registerUrl = "https://api.linkedin.com/v2/assets?action=registerUpload";
    //             $registerPayload = [
    //                 "registerUploadRequest" => [
    //                     "owner" => $author_urn,
    //                     "recipes" => ["urn:li:digitalmediaRecipe:feedshare-image"],
    //                     "serviceRelationships" => [[
    //                         "relationshipType" => "OWNER",
    //                         "identifier" => "urn:li:userGeneratedContent"
    //                     ]]
    //                 ]
    //             ];
    //             $headers = [
    //                 "Authorization: Bearer $accessToken",
    //                 "Content-Type: application/json"
    //             ];
    
    //             $ch = curl_init($registerUrl);
    //             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //             curl_setopt($ch, CURLOPT_POST, true);
    //             curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($registerPayload));
    //             curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //             $registerResponse = json_decode(curl_exec($ch), true);
    //             curl_close($ch);
    
    //             $uploadUrl = $registerResponse['value']['uploadMechanism']['com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest']['uploadUrl'] ?? null;
    //             $mediaUrn = $registerResponse['value']['asset'] ?? null;
    
    //             if (!$uploadUrl || !$mediaUrn) {
    //                 return [422, json_encode(['error' => 'Image upload registration failed.'])];
    //             }
    
    //             // STEP 2: Upload image bytes to LinkedIn
    //             $imageData = file_get_contents($image_url);
    //             if ($imageData === false) {
    //                 return [422, json_encode(['error' => 'Failed to download image from URL'])];
    //             }
    
    //             $uploadHeaders = [
    //                 "Content-Type: application/octet-stream"
    //             ];
    //             $ch = curl_init($uploadUrl);
    //             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //             curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    //             curl_setopt($ch, CURLOPT_POSTFIELDS, $imageData);
    //             curl_setopt($ch, CURLOPT_HTTPHEADER, $uploadHeaders);
    //             curl_exec($ch);
    //             $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //             curl_close($ch);
    
    //             if ($http_code !== 201 && $http_code !== 200) {
    //                 return [$http_code, json_encode(['error' => 'Failed to upload image to LinkedIn'])];
    //             }
    //         }
    
    //         // STEP 3: Create the post payload
    //         $url = 'https://api.linkedin.com/v2/ugcPosts';
    
    //         $shareContent = [
    //             "shareCommentary" => ["text" => $text],
    //             "shareMediaCategory" => "NONE"
    //         ];
    
    //         if ($mediaUrn) {
    //             $shareContent = [
    //                 "shareCommentary" => ["text" => $text],
    //                 "shareMediaCategory" => "IMAGE",
    //                 "media" => [
    //                     [
    //                         "status" => "READY",
    //                         "description" => ["text" => $text],
    //                         "media" => $mediaUrn,
    //                         "title" => ["text" => "Image"]
    //                     ]
    //                 ]
    //             ];
    //         }
    
    //         if ($video_url) {
    //             // (OPTIONAL) If needed, similar video upload logic can be implemented here.
    //         }
    
    //         $payload = [
    //             "author" => $author_urn,
    //             "lifecycleState" => "PUBLISHED",
    //             "specificContent" => [
    //                 "com.linkedin.ugc.ShareContent" => $shareContent
    //             ],
    //             "visibility" => [
    //                 "com.linkedin.ugc.MemberNetworkVisibility" => "PUBLIC"
    //             ]
    //         ];
    
    //         $headers = [
    //             "Authorization: Bearer $accessToken",
    //             "Content-Type: application/json",
    //             "X-Restli-Protocol-Version: 2.0.0"
    //         ];
    
    //         $ch = curl_init($url);
    //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //         curl_setopt($ch, CURLOPT_POST, true);
    //         curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    //         curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //         $response = curl_exec($ch);
    //         $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //         curl_close($ch);
    
    //         return [$http_status, $response];
    
    //     } catch (\Exception $e) {
    //         return [500, json_encode(['error' => $e->getMessage()])];
    //     }
    // }
    
    
    // public function shareOnLinkedIn($accessToken, $author_urn, $text, $image_url = null, $video_url = null) {
    //     try {
    //         $mediaUrn = null;
    //         $shareMediaCategory = "NONE";
    
    //         // STEP 1: Handle image
    //         if ($image_url && !$video_url) {
    //             $registerUrl = "https://api.linkedin.com/v2/assets?action=registerUpload";
    //             $registerPayload = [
    //                 "registerUploadRequest" => [
    //                     "owner" => $author_urn,
    //                     "recipes" => ["urn:li:digitalmediaRecipe:feedshare-image"],
    //                     "serviceRelationships" => [[
    //                         "relationshipType" => "OWNER",
    //                         "identifier" => "urn:li:userGeneratedContent"
    //                     ]]
    //                 ]
    //             ];
    //             $headers = [
    //                 "Authorization: Bearer $accessToken",
    //                 "Content-Type: application/json"
    //             ];
    //             $ch = curl_init($registerUrl);
    //             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //             curl_setopt($ch, CURLOPT_POST, true);
    //             curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($registerPayload));
    //             curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //             $registerResponse = json_decode(curl_exec($ch), true);
    //             curl_close($ch);
    
    //             $uploadUrl = $registerResponse['value']['uploadMechanism']['com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest']['uploadUrl'] ?? null;
    //             $mediaUrn = $registerResponse['value']['asset'] ?? null;
    
    //             if (!$uploadUrl || !$mediaUrn) {
    //                 return [422, json_encode(['error' => 'Image upload registration failed'])];
    //             }
    
    //             $imageData = file_get_contents($image_url);
    //             if ($imageData === false) {
    //                 return [422, json_encode(['error' => 'Failed to download image'])];
    //             }
    
    //             $uploadHeaders = ["Content-Type: application/octet-stream"];
    //             $ch = curl_init($uploadUrl);
    //             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //             curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    //             curl_setopt($ch, CURLOPT_POSTFIELDS, $imageData);
    //             curl_setopt($ch, CURLOPT_HTTPHEADER, $uploadHeaders);
    //             curl_exec($ch);
    //             $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //             curl_close($ch);
    
    //             if ($http_code !== 201 && $http_code !== 200) {
    //                 return [$http_code, json_encode(['error' => 'Image upload failed'])];
    //             }
    
    //             $shareMediaCategory = "IMAGE";
    //         }
    
    //         // STEP 2: Handle video
    //         if ($video_url) {
    //             $registerUrl = "https://api.linkedin.com/v2/assets?action=registerUpload";
    //             $registerPayload = [
    //                 "registerUploadRequest" => [
    //                     "owner" => $author_urn,
    //                     "recipes" => ["urn:li:digitalmediaRecipe:feedshare-video"],
    //                     "serviceRelationships" => [[
    //                         "relationshipType" => "OWNER",
    //                         "identifier" => "urn:li:userGeneratedContent"
    //                     ]]
    //                 ]
    //             ];
    //             $headers = [
    //                 "Authorization: Bearer $accessToken",
    //                 "Content-Type: application/json"
    //             ];
    //             $ch = curl_init($registerUrl);
    //             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //             curl_setopt($ch, CURLOPT_POST, true);
    //             curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($registerPayload));
    //             curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //             $registerResponse = json_decode(curl_exec($ch), true);
    //             curl_close($ch);
    
    //             $uploadUrl = $registerResponse['value']['uploadMechanism']['com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest']['uploadUrl'] ?? null;
    //             $mediaUrn = $registerResponse['value']['asset'] ?? null;
    
    //             if (!$uploadUrl || !$mediaUrn) {
    //                 return [422, json_encode(['error' => 'Video upload registration failed'])];
    //             }
    
    //             $videoData = file_get_contents($video_url);
    //             if ($videoData === false) {
    //                 return [422, json_encode(['error' => 'Failed to download video'])];
    //             }
    
    //             $uploadHeaders = ["Content-Type: application/octet-stream"];
    //             $ch = curl_init($uploadUrl);
    //             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //             curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    //             curl_setopt($ch, CURLOPT_POSTFIELDS, $videoData);
    //             curl_setopt($ch, CURLOPT_HTTPHEADER, $uploadHeaders);
    //             curl_exec($ch);
    //             $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //             curl_close($ch);
    
    //             if ($http_code !== 201 && $http_code !== 200) {
    //                 return [$http_code, json_encode(['error' => 'Video upload failed'])];
    //             }
    
    //             $shareMediaCategory = "VIDEO";
    //         }
    
    //         // STEP 3: Prepare post payload
    //         $shareContent = [
    //             "shareCommentary" => ["text" => $text],
    //             "shareMediaCategory" => $shareMediaCategory
    //         ];
    
    //         if ($mediaUrn && $shareMediaCategory !== "NONE") {
    //             $shareContent["media"] = [[
    //                 "status" => "READY",
    //                 "description" => ["text" => $text],
    //                 "media" => $mediaUrn,
    //                 "title" => ["text" => $shareMediaCategory === "IMAGE" ? "Image" : "Video"]
    //             ]];
    //         }
    
    //         $payload = [
    //             "author" => $author_urn,
    //             "lifecycleState" => "PUBLISHED",
    //             "specificContent" => [
    //                 "com.linkedin.ugc.ShareContent" => $shareContent
    //             ],
    //             "visibility" => [
    //                 "com.linkedin.ugc.MemberNetworkVisibility" => "PUBLIC"
    //             ]
    //         ];
    
    //         $headers = [
    //             "Authorization: Bearer $accessToken",
    //             "Content-Type: application/json",
    //             "X-Restli-Protocol-Version: 2.0.0"
    //         ];
    
    //         $ch = curl_init("https://api.linkedin.com/v2/ugcPosts");
    //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //         curl_setopt($ch, CURLOPT_POST, true);
    //         curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    //         curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //         $response = curl_exec($ch);
    //         $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //         curl_close($ch);
    
    //         return [$http_status, $response];
    
    //     } catch (\Exception $e) {
    //         return [500, json_encode(['error' => $e->getMessage()])];
    //     }
    // }
    
    
    public function shareOnLinkedIn($accessToken, $author_urn, $text, $image_file = null, $video_file = null) {
        try {
            $mediaUrn = null;
            $shareMediaCategory = "NONE";
    
            // STEP 1: Handle image upload
            if ($image_file && !$video_file) {
                $registerUrl = "https://api.linkedin.com/v2/assets?action=registerUpload";
                $registerPayload = [
                    "registerUploadRequest" => [
                        "owner" => $author_urn,
                        "recipes" => ["urn:li:digitalmediaRecipe:feedshare-image"],
                        "serviceRelationships" => [[
                            "relationshipType" => "OWNER",
                            "identifier" => "urn:li:userGeneratedContent"
                        ]]
                    ]
                ];
                $headers = [
                    "Authorization: Bearer $accessToken",
                    "Content-Type: application/json"
                ];
    
                $ch = curl_init($registerUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($registerPayload));
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $registerResponse = json_decode(curl_exec($ch), true);
                curl_close($ch);
    
                $uploadUrl = $registerResponse['value']['uploadMechanism']['com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest']['uploadUrl'] ?? null;
                $mediaUrn = $registerResponse['value']['asset'] ?? null;
    
                if (!$uploadUrl || !$mediaUrn) {
                    return [422, json_encode(['error' => 'Image upload registration failed'])];
                }
    
                $imageData = file_get_contents($image_file);
                if ($imageData === false) {
                    return [422, json_encode(['error' => 'Failed to read image file'])];
                }
    
                $uploadHeaders = ["Content-Type: application/octet-stream"];
                $ch = curl_init($uploadUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $imageData);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $uploadHeaders);
                curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
    
                if ($http_code !== 201 && $http_code !== 200) {
                    return [$http_code, json_encode(['error' => 'Image upload failed'])];
                }
    
                $shareMediaCategory = "IMAGE";
            }
    
            // STEP 2: Handle video upload
            if ($video_file) {
                $registerUrl = "https://api.linkedin.com/v2/assets?action=registerUpload";
                $registerPayload = [
                    "registerUploadRequest" => [
                        "owner" => $author_urn,
                        "recipes" => ["urn:li:digitalmediaRecipe:feedshare-video"],
                        "serviceRelationships" => [[
                            "relationshipType" => "OWNER",
                            "identifier" => "urn:li:userGeneratedContent"
                        ]]
                    ]
                ];
                $headers = [
                    "Authorization: Bearer $accessToken",
                    "Content-Type: application/json"
                ];
    
                $ch = curl_init($registerUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($registerPayload));
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $registerResponse = json_decode(curl_exec($ch), true);
                curl_close($ch);
    
                $uploadUrl = $registerResponse['value']['uploadMechanism']['com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest']['uploadUrl'] ?? null;
                $mediaUrn = $registerResponse['value']['asset'] ?? null;
    
                if (!$uploadUrl || !$mediaUrn) {
                    return [422, json_encode(['error' => 'Video upload registration failed'])];
                }
    
                $videoData = file_get_contents($video_file);
                if ($videoData === false) {
                    return [422, json_encode(['error' => 'Failed to read video file'])];
                }
    
                $uploadHeaders = ["Content-Type: application/octet-stream"];
                $ch = curl_init($uploadUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $videoData);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $uploadHeaders);
                curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
    
                if ($http_code !== 201 && $http_code !== 200) {
                    return [$http_code, json_encode(['error' => 'Video upload failed'])];
                }
    
                $shareMediaCategory = "VIDEO";
            }
    
            // STEP 3: Create post payload
            $shareContent = [
                "shareCommentary" => ["text" => $text],
                "shareMediaCategory" => $shareMediaCategory
            ];
    
            if ($mediaUrn && $shareMediaCategory !== "NONE") {
                $shareContent["media"] = [[
                    "status" => "READY",
                    "description" => ["text" => $text],
                    "media" => $mediaUrn,
                    "title" => ["text" => $shareMediaCategory === "IMAGE" ? "Image" : "Video"]
                ]];
            }
    
            $payload = [
                "author" => $author_urn,
                "lifecycleState" => "PUBLISHED",
                "specificContent" => [
                    "com.linkedin.ugc.ShareContent" => $shareContent
                ],
                "visibility" => [
                    "com.linkedin.ugc.MemberNetworkVisibility" => "PUBLIC"
                ]
            ];
    
            $headers = [
                "Authorization: Bearer $accessToken",
                "Content-Type: application/json",
                "X-Restli-Protocol-Version: 2.0.0"
            ];
    
            $ch = curl_init("https://api.linkedin.com/v2/ugcPosts");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec($ch);
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
    
            return [$http_status, $response];
    
        } catch (\Exception $e) {
            return [500, json_encode(['error' => $e->getMessage()])];
        }
    }
    
}
