<?hh

namespace Ecstatic\Routing;

class Route 
{
    private Vector $tokens = Vector {};
    private Map $params = Map {};

    public function __construct(
        private string $name,
        private string $method,
        private string $pattern,
        private string $controller,
        )
    {
    }

    public function getName(): string 
    {
        return $this->name;
    }

    public function setName(string $name): Route 
    {
        $this->name = $name;

        return $this;
    }

    public function getPattern(): string 
    {
        return $this->pattern;
    }

    public function setPattern(string $pattern): Route 
    {
        $this->pattern = $pattern;

        return $this;
    }

    public function getController(): string 
    {
        return $this->controller;
    }

    public function setController(string $controller): Route 
    {
        $this->controller = $controller;

        return $this;
    }    

    public function getMethod(): string 
    {
        return $this->method;
    }

    public function setMethod(string $method): Route 
    {
        $this->method = $method;

        return $this;
    }

    public function getTokens(): Vector {
        return $this->tokens;
    }

    public function setTokens(Vector $tokens): Route 
    {
        $this->tokens = $tokens;

        return $this;
    }

    public function getParams(): Map {
        return $this->params;
    }

    public function setParams(Map $params): Route 
    {
        $this->params = $params;

        return $this;
    }

}