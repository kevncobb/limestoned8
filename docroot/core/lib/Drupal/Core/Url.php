<?php

namespace Drupal\Core;

use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Utility\UnroutedUrlAssemblerInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines an object that holds information about a URL.
 */
class Url implements TrustedCallbackInterface {
  use DependencySerializationTrait;

  /**
   * The URL generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * The unrouted URL assembler.
   *
   * @var \Drupal\Core\Utility\UnroutedUrlAssemblerInterface
   */
  protected $urlAssembler;

  /**
   * The access manager
   *
   * @var \Drupal\Core\Access\AccessManagerInterface
   */
  protected $accessManager;

  /**
   * The route name.
   *
   * @var string
   */
  protected $routeName;

  /**
   * The route parameters.
   *
   * @var array
   */
  protected $routeParameters = [];

  /**
   * The URL options.
   *
   * See \Drupal\Core\Url::fromUri() for details on the options.
   *
   * @var array
   */
  protected $options = [];

  /**
   * Indicates whether this object contains an external URL.
   *
   * @var bool
   */
  protected $external = FALSE;

  /**
   * Indicates whether this URL is for a URI without a Drupal route.
   *
   * @var bool
   */
  protected $unrouted = FALSE;

  /**
   * The non-route URI.
   *
   * Only used if self::$unrouted is TRUE.
   *
   * @var string
   */
  protected $uri;

  /**
   * Stores the internal path, if already requested by getInternalPath().
   *
   * @var string
   */
  protected $internalPath;

  /**
   * Constructs a new Url object.
   *
   * In most cases, use Url::fromRoute() or Url::fromUri() rather than
   * constructing Url objects directly in order to avoid ambiguity and make your
   * code more self-documenting.
   *
   * @param string $route_name
   *   The name of the route
   * @param array $route_parameters
   *   (optional) An associative array of parameter names and values.
   * @param array $options
   *   See \Drupal\Core\Url::fromUri() for details.
   *
   * @see static::fromRoute()
   * @see static::fromUri()
   *
   * @todo Update this documentation for non-routed URIs in
   *   https://www.drupal.org/node/2346787
   */
  public function __construct($route_name, $route_parameters = [], $options = []) {
    $this->routeName = $route_name;
    $this->routeParameters = $route_parameters;
    $this->options = $options;
  }

  /**
   * Creates a new Url object for a URL that has a Drupal route.
   *
   * This method is for URLs that have Drupal routes (that is, most pages
   * generated by Drupal). For non-routed local URIs relative to the base
   * path (like robots.txt) use Url::fromUri() with the base: scheme.
   *
   * @param string $route_name
   *   The name of the route
   * @param array $route_parameters
   *   (optional) An associative array of route parameter names and values.
   * @param array $options
   *   See \Drupal\Core\Url::fromUri() for details.
   *
   * @return static
   *   A new Url object for a routed (internal to Drupal) URL.
   *
   * @see \Drupal\Core\Url::fromUserInput()
   * @see \Drupal\Core\Url::fromUri()
   */
  public static function fromRoute($route_name, $route_parameters = [], $options = []) {
    return new static($route_name, $route_parameters, $options);
  }

  /**
   * Creates a new URL object from a route match.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   *
   * @return static
   */
  public static function fromRouteMatch(RouteMatchInterface $route_match) {
    if ($route_match->getRouteObject()) {
      return new static($route_match->getRouteName(), $route_match->getRawParameters()->all());
    }
    else {
      throw new \InvalidArgumentException('Route required');
    }
  }

  /**
   * Creates a Url object for a relative URI reference submitted by user input.
   *
   * Use this method to create a URL for user-entered paths that may or may not
   * correspond to a valid Drupal route.
   *
   * @param string $user_input
   *   User input for a link or path. The first character must be one of the
   *   following characters:
   *   - '/': A path within the current site. This path might be to a Drupal
   *     route (e.g., '/admin'), to a file (e.g., '/README.txt'), or to
   *     something processed by a non-Drupal script (e.g.,
   *     '/not/a/drupal/page'). If the path matches a Drupal route, then the
   *     URL generation will include Drupal's path processors (e.g.,
   *     language-prefixing and aliasing). Otherwise, the URL generation will
   *     just append the passed-in path to Drupal's base path.
   *   - '?': A query string for the current page or resource.
   *   - '#': A fragment (jump-link) on the current page or resource.
   *   This helps reduce ambiguity for user-entered links and paths, and
   *   supports user interfaces where users may normally use auto-completion
   *   to search for existing resources, but also may type one of these
   *   characters to link to (e.g.) a specific path on the site.
   *   (With regard to the URI specification, the user input is treated as a
   *   @link https://tools.ietf.org/html/rfc3986#section-4.2 relative URI reference @endlink
   *   where the relative part is of type
   *   @link https://tools.ietf.org/html/rfc3986#section-3.3 path-abempty @endlink.)
   * @param array $options
   *   (optional) An array of options. See Url::fromUri() for details.
   *
   * @return static
   *   A new Url object based on user input.
   *
   * @throws \InvalidArgumentException
   *   Thrown when the user input does not begin with one of the following
   *   characters: '/', '?', or '#'.
   */
  public static function fromUserInput($user_input, $options = []) {
    // Ensuring one of these initial characters also enforces that what is
    // passed is a relative URI reference rather than an absolute URI,
    // because these are URI reserved characters that a scheme name may not
    // start with.
    if ((strpos($user_input, '/') !== 0) && (strpos($user_input, '#') !== 0) && (strpos($user_input, '?') !== 0)) {
      throw new \InvalidArgumentException("The user-entered string '$user_input' must begin with a '/', '?', or '#'.");
    }

    // fromUri() requires an absolute URI, so prepend the appropriate scheme
    // name.
    return static::fromUri('internal:' . $user_input, $options);
  }

  /**
   * Creates a new Url object from a URI.
   *
   * This method is for generating URLs for URIs that:
   * - do not have Drupal routes: both external URLs and unrouted local URIs
   *   like base:robots.txt
   * - do have a Drupal route but have a custom scheme to simplify linking.
   *   Currently, there is only the entity: scheme (This allows URIs of the
   *   form entity:{entity_type}/{entity_id}. For example: entity:node/1
   *   resolves to the entity.node.canonical route with a node parameter of 1.)
   *
   * For URLs that have Drupal routes (that is, most pages generated by Drupal),
   * use Url::fromRoute().
   *
   * @param string $uri
   *   The URI of the resource including the scheme. For user input that may
   *   correspond to a Drupal route, use internal: for the scheme. For paths
   *   that are known not to be handled by the Drupal routing system (such as
   *   static files), use base: for the scheme to get a link relative to the
   *   Drupal base path (like the <base> HTML element). For a link to an entity
   *   you may use entity:{entity_type}/{entity_id} URIs. The internal: scheme
   *   should be avoided except when processing actual user input that may or
   *   may not correspond to a Drupal route. Normally use Url::fromRoute() for
   *   code linking to any any Drupal page.
   * @param array $options
   *   (optional) An associative array of additional URL options, with the
   *   following elements:
   *   - 'query': An array of query key/value-pairs (without any URL-encoding)
   *     to append to the URL.
   *   - 'fragment': A fragment identifier (named anchor) to append to the URL.
   *     Do not include the leading '#' character.
   *   - 'absolute': Defaults to FALSE. Whether to force the output to be an
   *     absolute link (beginning with http:). Useful for links that will be
   *     displayed outside the site, such as in an RSS feed.
   *   - 'attributes': An associative array of HTML attributes that will be
   *     added to the anchor tag if you use the \Drupal\Core\Link class to make
   *     the link.
   *   - 'language': An optional language object used to look up the alias
   *     for the URL. If $options['language'] is omitted, it defaults to the
   *     current language for the language type LanguageInterface::TYPE_URL.
   *   - 'https': Whether this URL should point to a secure location. If not
   *     defined, the current scheme is used, so the user stays on HTTP or HTTPS
   *     respectively. TRUE enforces HTTPS and FALSE enforces HTTP.
   *
   * @return static
   *   A new Url object with properties depending on the URI scheme. Call the
   *   access() method on this to do access checking.
   *
   * @throws \InvalidArgumentException
   *   Thrown when the passed in path has no scheme.
   *
   * @see \Drupal\Core\Url::fromRoute()
   * @see \Drupal\Core\Url::fromUserInput()
   */
  public static function fromUri($uri, $options = []) {
    // parse_url() incorrectly parses base:number/... as hostname:port/...
    // and not the scheme. Prevent that by prefixing the path with a slash.
    if (preg_match('/^base:\d/', $uri)) {
      $uri = str_replace('base:', 'base:/', $uri);
    }
    $uri_parts = parse_url($uri);
    if ($uri_parts === FALSE) {
      throw new \InvalidArgumentException("The URI '$uri' is malformed.");
    }
    // Support protocol-relative URLs.
    if (strpos($uri, '//') === 0) {
      $uri_parts['scheme'] = '';
    }
    // Support root-relative URLs.
    elseif (strpos($uri, '/') === 0) {
      $uri_parts['scheme'] = 'base';
      $uri = 'base:' . substr($uri, 1);
    }
    elseif (empty($uri_parts['scheme'])) {
      throw new \InvalidArgumentException("The URI '$uri' is invalid. You must use a valid URI scheme.");
    }
    $uri_parts += ['path' => ''];
    // Discard empty fragment in $options for consistency with parse_url().
    if (isset($options['fragment']) && strlen($options['fragment']) == 0) {
      unset($options['fragment']);
    }
    // Extract query parameters and fragment and merge them into $uri_options,
    // but preserve the original $options for the fallback case.
    $uri_options = $options;
    if (isset($uri_parts['fragment'])) {
      $uri_options += ['fragment' => $uri_parts['fragment']];
      unset($uri_parts['fragment']);
    }

    if (!empty($uri_parts['query'])) {
      $uri_query = [];
      parse_str($uri_parts['query'], $uri_query);
      $uri_options['query'] = isset($uri_options['query']) ? $uri_options['query'] + $uri_query : $uri_query;
      unset($uri_parts['query']);
    }

    if ($uri_parts['scheme'] === 'entity') {
      $url = static::fromEntityUri($uri_parts, $uri_options, $uri);
    }
    elseif ($uri_parts['scheme'] === 'internal') {
      $url = static::fromInternalUri($uri_parts, $uri_options);
    }
    elseif ($uri_parts['scheme'] === 'route') {
      $url = static::fromRouteUri($uri_parts, $uri_options, $uri);
    }
    else {
      $url = new static($uri, [], $options);
      if ($uri_parts['scheme'] !== 'base') {
        $url->external = TRUE;
        $url->setOption('external', TRUE);
      }
      $url->setUnrouted();
    }

    return $url;
  }

  /**
   * Create a new Url object for entity URIs.
   *
   * @param array $uri_parts
   *   Parts from an URI of the form entity:{entity_type}/{entity_id} as from
   *   parse_url().
   * @param array $options
   *   An array of options, see \Drupal\Core\Url::fromUri() for details.
   * @param string $uri
   *   The original entered URI.
   *
   * @return static
   *   A new Url object for an entity's canonical route.
   *
   * @throws \InvalidArgumentException
   *   Thrown if the entity URI is invalid.
   */
  protected static function fromEntityUri(array $uri_parts, array $options, $uri) {
    list($entity_type_id, $entity_id) = explode('/', $uri_parts['path'], 2);
    if ($uri_parts['scheme'] != 'entity' || $entity_id === '') {
      throw new \InvalidArgumentException("The entity URI '$uri' is invalid. You must specify the entity id in the URL. e.g., entity:node/1 for loading the canonical path to node entity with id 1.");
    }

    return new static("entity.$entity_type_id.canonical", [$entity_type_id => $entity_id], $options);
  }

  /**
   * Creates a new Url object for 'internal:' URIs.
   *
   * Important note: the URI minus the scheme can NOT simply be validated by a
   * \Drupal\Core\Path\PathValidatorInterface implementation. The semantics of
   * the 'internal:' URI scheme are different:
   * - PathValidatorInterface accepts paths without a leading slash (e.g.
   *   'node/add') as well as 2 special paths: '<front>' and '<none>', which are
   *   mapped to the correspondingly named routes.
   * - 'internal:' URIs store paths with a leading slash that represents the
   *   root — i.e. the front page — (e.g. 'internal:/node/add'), and doesn't
   *   have any exceptions.
   *
   * To clarify, a few examples of path plus corresponding 'internal:' URI:
   * - 'node/add' -> 'internal:/node/add'
   * - 'node/add?foo=bar' -> 'internal:/node/add?foo=bar'
   * - 'node/add#kitten' -> 'internal:/node/add#kitten'
   * - '<front>' -> 'internal:/'
   * - '<front>foo=bar' -> 'internal:/?foo=bar'
   * - '<front>#kitten' -> 'internal:/#kitten'
   * - '<none>' -> 'internal:'
   * - '<none>foo=bar' -> 'internal:?foo=bar'
   * - '<none>#kitten' -> 'internal:#kitten'
   *
   * Therefore, when using a PathValidatorInterface to validate 'internal:'
   * URIs, we must map:
   * - 'internal:' (path component is '')  to the special '<none>' path
   * - 'internal:/' (path component is '/') to the special '<front>' path
   * - 'internal:/some-path' (path component is '/some-path') to 'some-path'
   *
   * @param array $uri_parts
   *   Parts from an URI of the form internal:{path} as from parse_url().
   * @param array $options
   *   An array of options, see \Drupal\Core\Url::fromUri() for details.
   *
   * @return static
   *   A new Url object for a 'internal:' URI.
   *
   * @throws \InvalidArgumentException
   *   Thrown when the URI's path component doesn't have a leading slash.
   */
  protected static function fromInternalUri(array $uri_parts, array $options) {
    // Both PathValidator::getUrlIfValidWithoutAccessCheck() and 'base:' URIs
    // only accept/contain paths without a leading slash, unlike 'internal:'
    // URIs, for which the leading slash means "relative to Drupal root" and
    // "relative to Symfony app root" (just like in Symfony/Drupal 8 routes).
    if (empty($uri_parts['path'])) {
      $uri_parts['path'] = '<none>';
    }
    elseif ($uri_parts['path'] === '/') {
      $uri_parts['path'] = '<front>';
    }
    else {
      if ($uri_parts['path'][0] !== '/') {
        throw new \InvalidArgumentException("The internal path component '{$uri_parts['path']}' is invalid. Its path component must have a leading slash, e.g. internal:/foo.");
      }
      // Remove the leading slash.
      $uri_parts['path'] = substr($uri_parts['path'], 1);

      if (UrlHelper::isExternal($uri_parts['path'])) {
        throw new \InvalidArgumentException("The internal path component '{$uri_parts['path']}' is external. You are not allowed to specify an external URL together with internal:/.");
      }
    }

    $url = \Drupal::pathValidator()
      ->getUrlIfValidWithoutAccessCheck($uri_parts['path']) ?: static::fromUri('base:' . $uri_parts['path'], $options);
    // Allow specifying additional options.
    $url->setOptions($options + $url->getOptions());

    return $url;
  }

  /**
   * Creates a new Url object for 'route:' URIs.
   *
   * @param array $uri_parts
   *   Parts from an URI of the form route:{route_name};{route_parameters} as
   *   from parse_url(), where the path is the route name optionally followed by
   *   a ";" followed by route parameters in key=value format with & separators.
   * @param array $options
   *   An array of options, see \Drupal\Core\Url::fromUri() for details.
   * @param string $uri
   *   The original passed in URI.
   *
   * @return static
   *   A new Url object for a 'route:' URI.
   *
   * @throws \InvalidArgumentException
   *   Thrown when the route URI does not have a route name.
   */
  protected static function fromRouteUri(array $uri_parts, array $options, $uri) {
    $route_parts = explode(';', $uri_parts['path'], 2);
    $route_name = $route_parts[0];
    if ($route_name === '') {
      throw new \InvalidArgumentException("The route URI '$uri' is invalid. You must have a route name in the URI. e.g., route:system.admin");
    }
    $route_parameters = [];
    if (!empty($route_parts[1])) {
      parse_str($route_parts[1], $route_parameters);
    }

    return new static($route_name, $route_parameters, $options);
  }

  /**
   * Returns the Url object matching a request.
   *
   * SECURITY NOTE: The request path is not checked to be valid and accessible
   * by the current user to allow storing and reusing Url objects by different
   * users. The 'path.validator' service getUrlIfValid() method should be used
   * instead of this one if validation and access check is desired. Otherwise,
   * 'access_manager' service checkNamedRoute() method should be used on the
   * router name and parameters stored in the Url object returned by this
   * method.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   A request object.
   *
   * @return static
   *   A Url object. Warning: the object is created even if the current user
   *   would get an access denied running the same request via the normal page
   *   flow.
   *
   * @throws \Drupal\Core\Routing\MatchingRouteNotFoundException
   *   Thrown when the request cannot be matched.
   */
  public static function createFromRequest(Request $request) {
    // We use the router without access checks because URL objects might be
    // created and stored for different users.
    $result = \Drupal::service('router.no_access_checks')->matchRequest($request);
    $route_name = $result[RouteObjectInterface::ROUTE_NAME];
    $route_parameters = $result['_raw_variables']->all();
    return new static($route_name, $route_parameters);
  }

  /**
   * Sets this Url to encapsulate an unrouted URI.
   *
   * @return $this
   */
  protected function setUnrouted() {
    $this->unrouted = TRUE;
    // What was passed in as the route name is actually the URI.
    // @todo Consider fixing this in https://www.drupal.org/node/2346787.
    $this->uri = $this->routeName;
    // Set empty route name and parameters.
    $this->routeName = NULL;
    $this->routeParameters = [];
    return $this;
  }

  /**
   * Generates a URI string that represents the data in the Url object.
   *
   * The URI will typically have the scheme of route: even if the object was
   * constructed using an entity: or internal: scheme. A internal: URI that
   * does not match a Drupal route with be returned here with the base: scheme,
   * and external URLs will be returned in their original form.
   *
   * @return string
   *   A URI representation of the Url object data.
   */
  public function toUriString() {
    if ($this->isRouted()) {
      $uri = 'route:' . $this->routeName;
      if ($this->routeParameters) {
        $uri .= ';' . UrlHelper::buildQuery($this->routeParameters);
      }
    }
    else {
      $uri = $this->uri;
    }
    $query = !empty($this->options['query']) ? ('?' . UrlHelper::buildQuery($this->options['query'])) : '';
    $fragment = isset($this->options['fragment']) && strlen($this->options['fragment']) ? '#' . $this->options['fragment'] : '';
    return $uri . $query . $fragment;
  }

  /**
   * Indicates if this Url is external.
   *
   * @return bool
   */
  public function isExternal() {
    return $this->external;
  }

  /**
   * Indicates if this Url has a Drupal route.
   *
   * @return bool
   */
  public function isRouted() {
    return !$this->unrouted;
  }

  /**
   * Returns the route name.
   *
   * @return string
   *
   * @throws \UnexpectedValueException.
   *   If this is a URI with no corresponding route.
   */
  public function getRouteName() {
    if ($this->unrouted) {
      throw new \UnexpectedValueException('External URLs do not have an internal route name.');
    }

    return $this->routeName;
  }

  /**
   * Returns the route parameters.
   *
   * @return array
   *
   * @throws \UnexpectedValueException.
   *   If this is a URI with no corresponding route.
   */
  public function getRouteParameters() {
    if ($this->unrouted) {
      throw new \UnexpectedValueException('External URLs do not have internal route parameters.');
    }

    return $this->routeParameters;
  }

  /**
   * Sets the route parameters.
   *
   * @param array $parameters
   *   The array of parameters.
   *
   * @return $this
   *
   * @throws \UnexpectedValueException.
   *   If this is a URI with no corresponding route.
   */
  public function setRouteParameters($parameters) {
    if ($this->unrouted) {
      throw new \UnexpectedValueException('External URLs do not have route parameters.');
    }
    $this->routeParameters = $parameters;
    return $this;
  }

  /**
   * Sets a specific route parameter.
   *
   * @param string $key
   *   The key of the route parameter.
   * @param mixed $value
   *   The route parameter.
   *
   * @return $this
   *
   * @throws \UnexpectedValueException.
   *   If this is a URI with no corresponding route.
   */
  public function setRouteParameter($key, $value) {
    if ($this->unrouted) {
      throw new \UnexpectedValueException('External URLs do not have route parameters.');
    }
    $this->routeParameters[$key] = $value;
    return $this;
  }

  /**
   * Returns the URL options.
   *
   * @return array
   *   The array of options. See \Drupal\Core\Url::fromUri() for details on what
   *   it contains.
   */
  public function getOptions() {
    return $this->options;
  }

  /**
   * Gets a specific option.
   *
   * See \Drupal\Core\Url::fromUri() for details on the options.
   *
   * @param string $name
   *   The name of the option.
   *
   * @return mixed
   *   The value for a specific option, or NULL if it does not exist.
   */
  public function getOption($name) {
    if (!isset($this->options[$name])) {
      return NULL;
    }

    return $this->options[$name];
  }

  /**
   * Sets the URL options.
   *
   * @param array $options
   *   The array of options. See \Drupal\Core\Url::fromUri() for details on what
   *   it contains.
   *
   * @return $this
   */
  public function setOptions($options) {
    $this->options = $options;
    return $this;
  }

  /**
   * Sets a specific option.
   *
   * See \Drupal\Core\Url::fromUri() for details on the options.
   *
   * @param string $name
   *   The name of the option.
   * @param mixed $value
   *   The option value.
   *
   * @return $this
   */
  public function setOption($name, $value) {
    $this->options[$name] = $value;
    return $this;
  }

  /**
   * Merges the URL options with any currently set.
   *
   * In the case of conflict with existing options, the new options will replace
   * the existing options.
   *
   * @param array $options
   *   The array of options. See \Drupal\Core\Url::fromUri() for details on what
   *   it contains.
   *
   * @return $this
   */
  public function mergeOptions($options) {
    $this->options = NestedArray::mergeDeep($this->options, $options);
    return $this;
  }

  /**
   * Returns the URI value for this Url object.
   *
   * Only to be used if self::$unrouted is TRUE.
   *
   * @return string
   *   A URI not connected to a route. May be an external URL.
   *
   * @throws \UnexpectedValueException
   *   Thrown when the URI was requested for a routed URL.
   */
  public function getUri() {
    if (!$this->unrouted) {
      throw new \UnexpectedValueException('This URL has a Drupal route, so the canonical form is not a URI.');
    }

    return $this->uri;
  }

  /**
   * Sets the value of the absolute option for this Url.
   *
   * @param bool $absolute
   *   (optional) Whether to make this Url absolute or not. Defaults to TRUE.
   *
   * @return $this
   */
  public function setAbsolute($absolute = TRUE) {
    $this->options['absolute'] = $absolute;
    return $this;
  }

  /**
   * Generates the string URL representation for this Url object.
   *
   * For an external URL, the string will contain the input plus any query
   * string or fragment specified by the options array.
   *
   * If this Url object was constructed from a Drupal route or from an internal
   * URI (URIs using the internal:, base:, or entity: schemes), the returned
   * string will either be a relative URL like /node/1 or an absolute URL like
   * http://example.com/node/1 depending on the options array, plus any
   * specified query string or fragment.
   *
   * @param bool $collect_bubbleable_metadata
   *   (optional) Defaults to FALSE. When TRUE, both the generated URL and its
   *   associated bubbleable metadata are returned.
   *
   * @return string|\Drupal\Core\GeneratedUrl
   *   A string URL.
   *   When $collect_bubbleable_metadata is TRUE, a GeneratedUrl object is
   *   returned, containing the generated URL plus bubbleable metadata.
   */
  public function toString($collect_bubbleable_metadata = FALSE) {
    if ($this->unrouted) {
      return $this->unroutedUrlAssembler()->assemble($this->getUri(), $this->getOptions(), $collect_bubbleable_metadata);
    }

    return $this->urlGenerator()->generateFromRoute($this->getRouteName(), $this->getRouteParameters(), $this->getOptions(), $collect_bubbleable_metadata);
  }

  /**
   * Returns the route information for a render array.
   *
   * @return array
   *   An associative array suitable for a render array.
   */
  public function toRenderArray() {
    $render_array = [
      '#url' => $this,
      '#options' => $this->getOptions(),
    ];
    if (!$this->unrouted) {
      $render_array['#access_callback'] = [get_class(), 'renderAccess'];
    }
    return $render_array;
  }

  /**
   * Returns the internal path (system path) for this route.
   *
   * This path will not include any prefixes, fragments, or query strings.
   *
   * @return string
   *   The internal path for this route.
   *
   * @throws \UnexpectedValueException.
   *   If this is a URI with no corresponding system path.
   */
  public function getInternalPath() {
    if ($this->unrouted) {
      throw new \UnexpectedValueException('Unrouted URIs do not have internal representations.');
    }

    if (!isset($this->internalPath)) {
      $this->internalPath = $this->urlGenerator()->getPathFromRoute($this->getRouteName(), $this->getRouteParameters());
    }
    return $this->internalPath;
  }

  /**
   * Checks this Url object against applicable access check services.
   *
   * Determines whether the route is accessible or not.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   (optional) Run access checks for this account. Defaults to the current
   *   user.
   *
   * @return bool
   *   Returns TRUE if the user has access to the url, otherwise FALSE.
   */
  public function access(AccountInterface $account = NULL) {
    if ($this->isRouted()) {
      return $this->accessManager()->checkNamedRoute($this->getRouteName(), $this->getRouteParameters(), $account);
    }
    return TRUE;
  }

  /**
   * Checks a Url render element against applicable access check services.
   *
   * @param array $element
   *   A render element as returned from \Drupal\Core\Url::toRenderArray().
   *
   * @return bool
   *   Returns TRUE if the current user has access to the url, otherwise FALSE.
   */
  public static function renderAccess(array $element) {
    return $element['#url']->access();
  }

  /**
   * @return \Drupal\Core\Access\AccessManagerInterface
   */
  protected function accessManager() {
    if (!isset($this->accessManager)) {
      $this->accessManager = \Drupal::service('access_manager');
    }
    return $this->accessManager;
  }

  /**
   * Gets the URL generator.
   *
   * @return \Drupal\Core\Routing\UrlGeneratorInterface
   *   The URL generator.
   */
  protected function urlGenerator() {
    if (!$this->urlGenerator) {
      $this->urlGenerator = \Drupal::urlGenerator();
    }
    return $this->urlGenerator;
  }

  /**
   * Gets the unrouted URL assembler for non-Drupal URLs.
   *
   * @return \Drupal\Core\Utility\UnroutedUrlAssemblerInterface
   *   The unrouted URL assembler.
   */
  protected function unroutedUrlAssembler() {
    if (!$this->urlAssembler) {
      $this->urlAssembler = \Drupal::service('unrouted_url_assembler');
    }
    return $this->urlAssembler;
  }

  /**
   * Sets the URL generator.
   *
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   (optional) The URL generator, specify NULL to reset it.
   *
   * @return $this
   */
  public function setUrlGenerator(UrlGeneratorInterface $url_generator = NULL) {
    $this->urlGenerator = $url_generator;
    $this->internalPath = NULL;
    return $this;
  }

  /**
   * Sets the unrouted URL assembler.
   *
   * @param \Drupal\Core\Utility\UnroutedUrlAssemblerInterface $url_assembler
   *   The unrouted URL assembler.
   *
   * @return $this
   */
  public function setUnroutedUrlAssembler(UnroutedUrlAssemblerInterface $url_assembler) {
    $this->urlAssembler = $url_assembler;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return ['renderAccess'];
  }

}
