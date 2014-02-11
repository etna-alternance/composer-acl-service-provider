<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException,
    Behat\Behat\Event\SuiteEvent;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;

require_once(__DIR__ . "/../../vendor/autoload.php");

foreach (glob(__DIR__ . "/lib/*.php") as $file) {
    require $file;
}

putenv("APPLICATION_ENV=" . (getenv("APPLICATION_ENV") ?: "local.testing"));

/**
 * Features context.
 */
class FeatureContext extends BehatContext
{
    use ETNA\FeatureContext\RSA;
    use ETNA\FeatureContext\SilexApplication;
    use ETNA\FeatureContext\FixedTime;

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param   array   $parameters     context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        $this->base_url = "http://localhost:8080";
        $this->request  = [
            "headers" => [],
            "cookies" => [],
            "files"   => [],
        ];
    }

    /**
     * @Given /^que j\'ai loader l\'acl-service-provider$/
     */
    public function queJAiLoaderLAclServiceProvider()
    {
        self::$silex_app->register(new \ETNA\Silex\Provider\Acl\AclServiceProvider());
    }

    /**
     * @Given /^setter le app\[\'auth\.app_name\'\] a "([^"]*)"$/
     */
    public function setterLeAppAuthAppNameA($arg1)
    {
        self::$silex_app["auth"] = [];
        self::$silex_app["auth.app_name"] = $arg1;
    }

    /**
     * @Given /^que je suis authentifié en tant que "([^"]*)"(?: depuis (\d+) minutes?)?(?: avec les roles "([^"]*)")?(?: avec l'id (\d+))?/
     */
    public function queJeSuisAuthentifieEnTantQue($login, $duration = 1, $roles = "", $id = 1)
    {
        $duration = (int) $duration;
        $id = (int) $id;

        $identity = base64_encode(json_encode([
            "id"         => $id,
            "login"      => $login,
            "logas"      => false,
            "groups"     => explode(",", $roles),
            "login_date" => date("Y-m-d H:i:s", strtotime("now -{$duration}minutes")),
        ]));

        $identity = [
            "identity"  => $identity,
            "signature" => self::$rsa->sign($identity),
        ];

        $this->request["cookies"]["authenticator"] = base64_encode(json_encode($identity));
    }

    /**
     * @When /^je fais un (GET|POST|PUT|DELETE) sur ((?:[a-zA-Z0-9,:!\/\.\?\&\=\+_%-]*)|"(?:[^"]+)")(?: avec le corps contenu dans "([^"]*\.json)")?$/
     */
    public function jeFaisUneRequetteHTTP($method, $url, $body = null)
    {
        if ($body !== null) {
            $body = @file_get_contents($this->requests_path . $body);
            if (!$body) {
               throw new Exception("File not found : {$this->requests_path}${body}");
            }
        }
        $this->jeFaisUneRequetteHTTPAvecDuJSON($method, $url, $body);
    }

    /**
     * @When /^je fais un (GET|POST|PUT|DELETE) sur ((?:[a-zA-Z0-9,:!\/\.\?\&\=\+_%-]*)|"(?:[^"]+)") avec le JSON suivant :$/
     */
    public function jeFaisUneRequetteHTTPAvecDuJSON($method, $url, $body)
    {
        if (preg_match('/^".*"$/', $url)) {
            $url = substr($url, 1, -1);
        }

        if ($body !== null) {
            if (is_object($body)) {
                $body = $body->getRaw();
            }
            $this->request["headers"]["Content-Type"] = 'application/json';
            //TODO add content-length ...
        }

        $request = Request::create($this->base_url . $url, $method, [], [], [], [], $body);
        $request->headers->add($this->request["headers"]);
        $request->cookies->add($this->request["cookies"]);
        $request->files->add($this->request["files"]);

        $response = self::$silex_app->handle($request, HttpKernelInterface::MASTER_REQUEST, true);

        $result = [
            "http_code"    => $response->getStatusCode(),
            "http_message" => Response::$statusTexts[$response->getStatusCode()],
            "body"         => $response->getContent(),
            "headers"      => array_map(function ($item) {
                return $item[0];
            }, $response->headers->all()),
        ];

        $this->response = $result;
    }

    /**
     * @Then /^le status HTTP devrait être (\d+)$/
     */
    public function leStatusHTTPDevraitEtre($code)
    {
        $retCode = $this->response["http_code"];
        if ("$retCode" !== "$code") {
            echo $this->response["body"];
            throw new Exception("Bad http response code {$retCode} != {$code}");
        }
    }

    /**
     * @Then /^je devrais avoir un résultat d\'API en JSON$/
     */
    public function jeDevraisAvoirUnResultatDApiEnJSON()
    {
        if ("application/json" !== $this->response["headers"]["content-type"]) {
            throw new Exception("Invalid response type");
        }
        if ($this->response['body'] == "") {
            throw new Exception("No response");
        }
        $json = json_decode($this->response['body']);

        if ($json === null && json_last_error()) {
            throw new Exception("Invalid response");
        }
        $this->data = $json;
    }

    /**
     * @Given /^le header "([^"]*)" doit être (\d+)$/
     */
    public function leHeaderDoitEtre($header, $value)
    {
        if ($this->response["headers"][strtolower($header)] != $value) {
            throw new Exception("Invalid header '{$header}'. Value should be '{$value}' but recieved '{$this->response["headers"][$header]}'");
        }
    }

    /**
     * @Then /^le résultat devrait être identique au fichier "(.*)"$/
     */
    public function leResultatDevraitRessemblerAuFichier($file)
    {
        $file = realpath($this->results_path . "/" . $file);
        $this->leResultatDevraitRessemblerAuJsonSuivant(file_get_contents($file));
    }

    /**
     * @Then /^le résultat devrait être identique à "(.*)"$/
     * @Then /^le résultat devrait être identique au JSON suivant :$/
     * @Then /^le résultat devrait ressembler au JSON suivant :$/
     */
    public function leResultatDevraitRessemblerAuJsonSuivant($string)
    {
        $result = json_decode($string);
        if ($result === null) {
            throw new Exception("json_decode error");
        }

        $this->check($result, $this->data, "result", $errors);
        if ($n = count($errors)) {
            echo json_encode($this->data, JSON_PRETTY_PRINT);
            throw new Exception("{$n} errors :\n" . implode("\n", $errors));
        }
    }

    protected function check($expected_value, $found_value, $prefix, &$errors)
    {
        if (is_string($expected_value) && $expected_value == "#Array#") {
            if (!is_array($found_value)) {
                $errors[] = sprintf("%-35s: not an array", $prefix);
            }

            return;
        }

        if (is_string($expected_value) && substr($expected_value, 0, 1) == "#" && substr($expected_value, -1, 1) == "#") {
            if (!preg_match($expected_value, $found_value)) {
                $errors[] = sprintf("%-35s: regex error : '%s' does not match '%s'", $prefix, $found_value, $expected_value);
            }

            return;
        }

        $t1 = gettype($expected_value);
        $t2 = gettype($found_value);
        if ($t1 != $t2) {
            $errors[] = sprintf("%-35s: type error : expected '%s'; got '%s'", $prefix, $t1, $t2);
            return;
        }

        if (is_array($expected_value)) {
            $l1 = count($expected_value);
            $l2 = count($found_value);
            if ($l1 != $l2) {
                $errors[] = sprintf("%-35s: array length error : expected '%d'; got '%d'", $prefix, $l1, $l2);
                return;
            }

            for ($i = 0 ; $i < $l1; $i++) {
                $this->check($expected_value[$i], $found_value[$i], "{$prefix}[{$i}]", $errors);
            }
            return;
        }

        if (is_object($expected_value)) {
            $expected_keys = array_keys((array) $expected_value);
            $found_keys    = array_keys((array) $found_value);

            foreach (array_diff($expected_keys, $found_keys) as $key) {
                $errors[] = sprintf("%-35s: missing key", "{$prefix}->{$key}", $key);
            }
            foreach (array_diff($found_keys, $expected_keys) as $key) {
                $errors[] = sprintf("%-35s: unexpected key", "{$prefix}->{$key}", $key);
            }

            foreach (array_intersect($expected_keys, $found_keys) as $key) {
                $this->check($expected_value->$key, $found_value->$key, "{$prefix}->{$key}", $errors);
            }

            return;
        }

        if ($expected_value !== $found_value) {
            $errors[] = sprintf("%-35s: value error : expected %s; got %s", $prefix, var_export($expected_value, true), var_export($found_value, true));
        }
    }
}
