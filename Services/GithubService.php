<?php
namespace Modules\Feishu\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Modules\Feishu\Foundation\Jwt;

class GithubService
{
    public function generateJwt()
    {
        $token = Jwt::encode([
            'iss' => env('GITHUB_APP_ID'),
            'iat' => time(),
            'exp' => time() + (5 * 60),
        ], env('GITHUB_APP_SECRET'), 'RS256');

        return $token;
    }

    /**
     * Get GitHub App Access Token
     *
     * @return string
     */
    public function getAppAccessToken()
    {
        $jwtToken = $this->generateJwt();

        return Cache::remember('github_app_access_token'.env('GITHUB_APP_INSTALL_ID'), 1 * 60 * 60, function () use ($jwtToken) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$jwtToken,
                'Accept' => 'application/vnd.github.v3+json',
            ])->post('https://api.github.com/app/installations/'.env('GITHUB_APP_INSTALL_ID').'/access_tokens');

            return $response->json('token');
        });
    }

    public function getRepositoryContent(string $owner, string $repo, string $path = '', string $type = 'object')
    {
        $response = Http::withToken($this->getAppAccessToken())
            ->withHeaders([
                'Accept' => 'Accept: application/vnd.github.object',
                'X-GitHub-Api-Version' => '2022-11-28'
            ])
            ->withoutVerifying()
            ->get("https://api.github.com/repos/{$owner}/{$repo}/contents/{$path}");

        return $response->json();
    }

    /**
     * Push content to GitHub Repository
     *
     * @param string $owner
     * @param string $repo
     * @param string $path
     * @param string $content
     * @param string $message
     * @return mixed
     * @see https://docs.github.com/en/rest/repos/contents?apiVersion=2022-11-28#create-or-update-file-contents
     */
    public function pushContent($params)
    {
        ['owner' => $owner, 'repo' => $repo, 'path' => $path, 'content' => $content, 'message' => $message] = $params;

        $data = [
            'message' => $message,
            'owner' => $owner,
            'repo' => $repo,
            'path' => $path,
            'sha' => $params['sha'] ?? '',
            'committer' => [
                'name' => 'chaosadmin',
                'email' => 'cdk.1997@outlook.com'
            ],
            'content' => base64_encode($content),
        ];

        if (!empty($params['branch'])) {
            $data['branch'] = $params['branch'];
        }
        $response = Http::withToken($this->getAppAccessToken())
            ->withHeaders([
                'Accept' => 'application/vnd.github.v3+json',
                'X-GitHub-Api-Version' => '2022-11-28'
            ])
            ->withoutVerifying()
            ->put("https://api.github.com/repos/$owner/$repo/contents/$path", $data);
    
        return $response->json();
    }
}