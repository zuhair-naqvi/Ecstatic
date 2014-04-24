<?hh

namespace Ecstatic\Routing;

require_once VENDOR . '/Ecstatic/Routing/Route.php';
require_once APP . '/Wishes/Controller/UserController.php';
require_once APP . '/Wishes/Controller/PostController.php';

use Ecstatic\Routing\Route as Route;

class Router {

	// Todo: Match trailing slash as well
	private ImmMap $lexerRules = ImmMap {
									'/(?=\/).([A-Za-z0-9_]{1,16})/'  => 'T_LITERAL',
									'/(?=\:).([A-Za-z0-9_]{1,16})/' => 'T_VARIABLE'
								};								
	private Vector <Route> $routes = Vector {};
	
	public function lex(string $pattern): array 
	{
		$tokens = array();

		foreach($this->lexerRules as $rule => $type) {	    	
	        if(preg_match_all($rule, $pattern, $matches, PREG_OFFSET_CAPTURE)) {
	        	$tokens = array_merge($tokens, array_map(function($match) use($type) {
	        		return Map {
	        			'type'   => $type,
	        			'match'  => $match[0],
	        			'offset' => $match[1]
	        		};
	        	}, $matches[1]));
	        }
	    }

	    usort($tokens, function($a, $b){
	    	return ($a['offset'] < $b['offset']) ? -1 : 1;
	    });
	    return $tokens;
	}

	public function parse(string $uri, string $method): Vector 
	{
		$uriTokens = explode('/', $uri);
		array_shift($uriTokens);
		$params = array();
		return $this->routes->filter($route ==> { if($route->getMethod() == $method) return $route; })
							->filter($route ==> {								
								if(count($route->getTokens()) != count($uriTokens)) return false;
								foreach ($route->getTokens() as $index => $token) {
									if($route->getTokens()->get($index)->get('type') == 'T_LITERAL' && $route->getTokens()->get($index)->get('match') != $uriTokens[$index]) {
										return false;
									}
									else if ($route->getTokens()->get($index)->get('type') == 'T_VARIABLE')
									{
										$params = array_merge($params, array($route->getTokens()->get($index)->get('match') => $uriTokens[$index]));
									}
								}
								$route->setParams(Map::fromArray($params));
								return $route;
							});
	}	

	public function resolve(string $uri): void 
	{
		global $container;

		$matches = $this->parse($uri, $_SERVER['REQUEST_METHOD']);

		if($matches->count() > 0) {
			$firstMatch = $matches->get(0);
			$controllerString = explode('::', $firstMatch->getController());
			$controllerName = $controllerString[0];
			$actionName = $controllerString[1];
			$controllerService = $container->get('Controllers')->filter($controller ==> { if(get_class($controller) == $controllerName) return $controller; });
			if($controllerService->count() > 0)
			{
				$controllerService->get(0)->$actionName($firstMatch->getParams());
			}
		}
		else {
			throw new \Exception("No matching routes", 1);
		}
	}

	public function index(): Router
	{
		global $container;

		foreach ($container->get('Routes') as $routeDefinition) {
			if(!apc_exists('Route_' . $routeDefinition['name'])) {

				$route = new Route($routeDefinition['name'], $routeDefinition['method'], $routeDefinition['pattern'], $routeDefinition['controller']);
				$route->setTokens(Vector::fromArray($this->lex($route->getPattern())));				

				apc_store('Route_' . $routeDefinition['name'], $route);
			}
			
			$this->routes->add(apc_fetch('Route_' . $routeDefinition['name']));
		}

		return $this;
	}

}