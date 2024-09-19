<?php

namespace WPLTK\App\Services;

class Router
{
    private $namespace = '';

    public function __construct($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * Main route registration method
     *
     * @param string $method The HTTP method (GET, POST, etc.)
     * @param string $endpoint The route endpoint
     * @param callable $callback The callback function
     * @param array|string $permissions The user capabilities required to access the route
     *
     * @return $this
     */
    public function route($method, $endpoint, $callback, $permissions = [])
    {
        // Replacing {id} with a named regex capture group for numeric IDs
        $endpoint = str_replace('{id}', '(?P<id>[\d]+)', $endpoint);

        register_rest_route($this->namespace, $endpoint, [
            'methods' => $method,
            'callback' => function ($request) use ($callback) {
                // Call the controller method and handle WP_Error responses
                $result = call_user_func($callback, $request);

                if (is_wp_error($result)) {
                    return new \WP_REST_Response([
                        'code' => $result->get_error_code(),
                        'message' => $result->get_error_message(),
                        'data' => $result->get_error_data()
                    ], 422);
                }

                // Clear any output buffer in case something is printed unintentionally
                ob_get_clean();

                // Return the response
                return rest_ensure_response($result);
            },
            'permission_callback' => function ($request) use ($permissions) {
                // Permission checking logic
                if (is_array($permissions) && !empty($permissions)) {
                    foreach ($permissions as $permission) {
                        if (current_user_can($permission)) {
                            return true;
                        }
                    }
                    return false;
                }

                // If no specific permissions, fallback to true
                return true;
            }
        ]);

        return $this;
    }

    /**
     * Register a route for GET requests
     */
    public function get($endpoint, $callback, $permissions = [])
    {
        return $this->route(\WP_REST_Server::READABLE, $endpoint, $callback, $permissions);
    }

    /**
     * Register a route for POST requests
     */
    public function post($endpoint, $callback, $permissions = [])
    {
        return $this->route(\WP_REST_Server::CREATABLE, $endpoint, $callback, $permissions);
    }

    /**
     * Register a route for PUT requests
     */
    public function put($endpoint, $callback, $permissions = [])
    {
        return $this->route(\WP_REST_Server::EDITABLE, $endpoint, $callback, $permissions);
    }

    /**
     * Register a route for PATCH requests
     */
    public function patch($endpoint, $callback, $permissions = [])
    {
        return $this->route(\WP_REST_Server::EDITABLE, $endpoint, $callback, $permissions);
    }

    /**
     * Register a route for DELETE requests
     */
    public function delete($endpoint, $callback, $permissions = [])
    {
        return $this->route(\WP_REST_Server::DELETABLE, $endpoint, $callback, $permissions);
    }
}
