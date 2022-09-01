# Linku API Documentation Bundle
This bundle can be used to modify OpenAPI documentation generated by API-Platform.

## Installation
You can add this bundle to your project using `composer require linku/api-documentation-bundle`.
Symfony flex should automatically add the bundle to the list of used bundles in 
your project. If not, add `Linku\ApiDocumentationBundle\LinkuApiDocumentationBundle::class => ['all' => true],`
to the `config/bundles.php` file.

For now, you need to manually add a `config/packages/linku_api_documentation.yaml`
file and put the configuration for your project there. An example configuration
can be found at [the end of this file](#configuration-example).

You also need to add the following route:
```yaml
linku_api_documentation:
    resource: "@LinkuApiDocumentationBundle/Resources/routing/sections.xml"
```

Optionally, you can add a `prefix:` to prefix all paths generated. This should be the
same prefix as used by API-Platform in their routes.

## Sections
Within the `sections` configuration, you can define a list of available sections.
The identifier used in the list can also be used to define sections on individual
API-Platform resources or endpoints.

Each section has a `prefix` (URI path prefix) and a `title`.

Using the original `/docs` route will show the section defined as default in `default_section`.
You can view documentation for other sections using `/{section_prefix}/docs`.

### Default usage
By default, all endpoints with a custom defined path that start with the `prefix`
of a section, will be added to that section. For example, a section with
`prefix: 'customer_portal'` will automatically contain all endpoints with custom paths
starting with `/customer_portal/`.

### Resource based
Within the API-Platform configuration, you can add a `sections` array to the resource
`attributes`. All endpoints within this resource will then be added to the sections
with those identifiers.

YAML Example:
```yaml
resources:
    App\Task\Task:
        attributes:
            sections: ['customerPortal']
```

### Endpoint based
Within the API-Platform configuration, you can add a `sections` array to an endpoint
definition. That endpoint will then be added to the sections with those identifiers.

YAML Example:
```yaml
resources:
    App\Task\Task:
        itemOperations:
            get:
                sections: ['customerPortal']
```

## Removal
Out-of-the-box, API-Platform does not allow you to remove query parameters or
responses without having to manually define all the remaining ones. As you can only
add or overwrite items, but not remove them entirely. This bundle supplies some help
to remove responses and parameters from generated documentation.

### Parameters
To remove a parameter, the endpoint `path` and `method` are required. As well as the
`name` of the parameter. These can be added to the `linku_api_documentation.removal.parameters`
list.

### Request bodies
To remove a request body, the endpoint `path` and `method` are required. These can be
added to the `linku_api_documentation.removal.request_bodies` list.

### Responses
To remove a response, the endpoint `path` and `method` are required. As well as the
`statusCode` of the response. These can be added to the `linku_api_documentation.removal.responses`
list.

## Configuration Example
```yaml
linku_api_documentation:
    sections:
        default:
            prefix: ''
            title: ''
        customerPortal:
            prefix: 'customer_portal'
            title: 'Customer Portal'
    default_section: 'default'

    removal:
        parameters:
            - path: '/users/me'
              method: 'get'
              name: 'uuid'

        request_bodies:
            - path: '/tasks/{id}/complete'
              method: 'put'

        responses:
            - path: '/users/update_credentials'
              method: 'post'
              statusCode: 201
            - path: '/users/update_credentials'
              method: 'post'
              statusCode: 422
```
