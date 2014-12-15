<?php

namespace Keboola\YoutubeExtractorBundle\Controller;

use Keboola\ExtractorBundle\Controller\OAuth20Controller;

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

	/**
	 * Create OAuth 2.0 request code URL (use CODE "response type")
	 * @param string $redirUrl Redirect URL
	 * @param string $hash Session verification code (use in the "state" query parameter)
	 * @return string
	 */
	protected function getOAuthUrl($redirUrl, $clientId,  $hash)
	{
		return "https://accounts.google.com/o/oauth2/auth?"
			. "response_type=code"
			. "&client_id={$clientId}"
			. "&redirect_uri={$redirUrl}"
			. "&scope=https://www.googleapis.com/auth/yt-analytics.readonly+https://www.googleapis.com/auth/yt-analytics-monetary.readonly"
			. "&state={$hash}"
			. "&access_type=offline"
			. "&approval_prompt=force";
	}

}
