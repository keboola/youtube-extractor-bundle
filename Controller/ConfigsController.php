<?php

namespace Keboola\YoutubeExtractorBundle\Controller;

use Keboola\ExtractorBundle\Controller\ConfigsController as Controller;

class ConfigsController extends Controller {
	protected $appName = "ex-youtube";
	protected $columns = ['api', 'endpoint', 'params', 'dataType', 'dataField', 'recursionParams'];
}
