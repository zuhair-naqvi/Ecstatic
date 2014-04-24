<?hh

namespace Esctatic\Controller;

class BaseController 
{
  public function __construct()
  {
    $this->publishRoutes();
  }

  public function publishRoutes(): void
  {
    global $container;
    
    $childClass = get_called_class();
    $reflectionObject = new \ReflectionClass($childClass);

    foreach($reflectionObject->getMethods() as $i => $method)
    {
      $className  = $childClass;
      $methodName = $method->name;
       foreach ($method->info['attributes'] as $key => $value) {
        if($key == 'GET' || $key == 'POST') {
          // echo $value;
          $container->get('Routes')->add(Map {
            'name' => str_replace('\\', '_', $className . '_' . $methodName),
            'method' => $key,
            'pattern' => $value[0],
            'controller' => $className . '::' .$methodName
          });
        }
       }
    }
  }

  public function render($template, $params): void
  {
    include(APP.'/Wishes/View/'.$template);
    echo $viewContent;
  }
}