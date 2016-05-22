<?php
namespace Afonso\LvCrud\Responses;

class HtmlResponseBuilder extends ResponseBuilder /* implements ResponseBuilderInterface*/
{
    public function build($data, $code = 200)
    {
        return view($this->getViewFromUri($this->request), ['data' => $data]);
    }

    private function getViewFromUri($request)
    {
        $uri = $request->path();
        $method = $request->method();

        $segments = explode('/', $uri);

        $view = $segments[0];
        if (count($segments) < 2) {
            if ($method === 'POST') {
                return "{$view}.store";
            }
            return "{$view}.index";
        } elseif (count($segments) === 2) {
            if ($method === 'GET') {
                if ($segments[1] === 'create') {
                    return "{$view}.create";
                }
                return "{$view}.show";
            }
            if ($method === 'DELETE') {
                return "{$view}.destroy";
            }
            if ($method === 'PUT') {
                return "{$view}.update";
            }
        } elseif (count($segments) === 3) {
            if ($segments[2] === 'edit') {
                return "{$view}.edit";
            }
        }

        throw new \RuntimeException("Cannot convert URI to view: {$uri}");
    }
}
