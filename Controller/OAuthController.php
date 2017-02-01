<?php

namespace Keboola\YoutubeExtractorBundle\Controller;

use Keboola\ExtractorBundle\Controller\OAuth20Controller;

use	Symfony\Component\HttpFoundation\Response,
	Symfony\Component\HttpFoundation\Request;

class OAuthController extends OAuth20Controller
{
	/**
	 * @var string
	 */
	protected $appName = "ex-youtube";

	/**
	 * OAuth 2.0 token retrieval URL
	 * See (C) at http://www.ibm.com/developerworks/library/x-androidfacebookapi/fig03.jpg
	 * @var string
	 */
	protected $tokenUrl = "https://accounts.google.com/o/oauth2/token";
	
	public function preExecute(Request $request)
        {
            parent::preExecute($request);
            Request::setTrustedProxies(array($request->server->get('REMOTE_ADDR')));
        }

	/**
	 * Create OAuth 2.0 request code URL (use CODE "response type")
	 * @param string $redirUrl Redirect URL
	 * @param string $hash Session verification code (use in the "state" query parameter)
	 * @return string
	 */
	protected function getOAuthUrl($redirUrl, $clientId,  $hash)
	{
		$scopes = implode('+', [
			'https://www.googleapis.com/auth/yt-analytics.readonly',
			'https://www.googleapis.com/auth/yt-analytics-monetary.readonly',
			'https://www.googleapis.com/auth/youtube.readonly',
			'https://www.googleapis.com/auth/youtubepartner',
			'https://www.googleapis.com/auth/youtubepartner-channel-audit'
		]);

		return "https://accounts.google.com/o/oauth2/auth?"
			. "response_type=code"
			. "&client_id={$clientId}"
			. "&redirect_uri={$redirUrl}"
			. "&scope={$scopes}"
			. "&state={$hash}"
			. "&access_type=offline"
			. "&approval_prompt=force";
	}

}
