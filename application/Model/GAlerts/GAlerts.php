<?

class Model_GAlertException extends Exception { }

class Model_GAlerts_GAlerts {

    protected $user;
    protected $pass;
    protected $cookie;
    protected $timeout;

    private $urlLogin='https://accounts.google.com/ServiceLogin?hl=en&service=alerts&continue=http://www.google.com/alerts/manage';
    private $urlAuth='https://accounts.google.com/ServiceLoginAuth';
    private $urlAlerts='http://www.google.com/alerts';
    private $urlCreate='http://www.google.com/alerts/create';
    private $urlManage='http://www.google.com/alerts/manage';
    private $urlDelete='http://www.google.com/alerts/save';

    private $maxRedirs=5;

    public function __construct($user, $pass, $cookiefile='cookies.txt', $timeout=30)
    {
        $this->user=$user;
        $this->pass=$pass;
        $this->cookie=$cookiefile;
        $this->timeout=$timeout;
    }

    /**
     * Creates a new alert in the system
     * @param string $query term to search for
     * @param string $lang language of the searches ('en', 'ca', 'es',...)
     * @param string $frequency when the alerts are refreshed. Possible values: 'day', 'week', 'happens'
     * @param string $type type of the returned results. Possible values: 'all', 'news', 'blogs', 'videos', 'forums','books'
     * @param string $quantity number of results all or just the best. Possible values: 'best', 'all'
     * @param string $dest destination of the alert. Possible values: 'feed', 'email'
     * @return array with the keys: ('id', 'type', 'term', 'url');
     * @throws Exception when the server returns an incorrect response
     */
    public function create($query, $lang='en', $frequency='happens', $type='all', $quantity='best', $dest='feed')
    {
        $e='feed';
        $t='7'; //all
        $f='0'; //when happens
        $l='0'; //only best

        if ($quantity!='best') $l=1;
        if ($dest=='email') $e=$this->user;

        switch ($type) {
            case 'news':
                $t='1';
                break;
            case 'blogs':
                $t='4';
                break;
            case 'videos':
                $t='9';
                break;
            case 'forums':
                $t='8';
                break;
            case 'books':
                $t='22';
                break;
        }

        switch ($frequency) {
            case 'day':
                $f=1;
                break;
            case 'week':
                $f=6;
                break;
        }

        $ch=$this->authentication();

        curl_setopt($ch, CURLOPT_URL, $this->urlAlerts.'?hl='.$lang);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, null);
        $result = curl_exec($ch);

        $posi=strpos($result, 'name="x" value="')+strlen('name="x" value="');
        $posf=strpos($result, '"', $posi+1);
        $tokenX=substr($result, $posi, $posf-$posi);

        curl_setopt($ch, CURLOPT_URL, $this->urlCreate.'?hl='.$lang);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'x='.$tokenX.'&q='.urlencode($query).'&t='.$t.'&f='.$f.'&l='.$l.'&e='.$e);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_REFERER, 'http://www.google.com/alerts?hl=es');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Origin: http://www.google.com',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.3',
            'Cache-Control: max-age=0',
            'Connection: keep-alive	'
        ));
        $result=curl_exec($ch);
        $state=curl_getinfo($ch);

        curl_setopt($ch, CURLOPT_URL, $this->urlManage.'?hl='.$lang);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, null);
        $result = curl_exec($ch);

        $alerts=$this->parseAlerts($result);
        if (count($alerts)<=0) {
            throw new GAlertException('Cannot create the alert, alert not found after creation.');
        }

        foreach ($alerts as $a) {
            if ($a['term']==$query && $a['type']==$dest) {
                return $a;
            }
        }

        throw new GAlertException('Cannot create the alert, alert not found after creation.');
    }

    /**
     * Deletes an alert from the system
     * @param str $idAlert Can be obtained with create() or list() methods
     * @return boolean true when success
     * @throws Exception when the server returns an incorrect response
     */
    public function delete($idAlert)
    {
        $ch=$this->authentication();

        curl_setopt($ch, CURLOPT_URL, $this->urlManage.'?hl=en');
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, null);
        $result = curl_exec($ch);

        $posi=strpos($result, 'name="x" value="')+strlen('name="x" value="');
        $posf=strpos($result, '"', $posi+1);
        $tokenX=substr($result, $posi, $posf-$posi);

        curl_setopt($ch, CURLOPT_URL, $this->urlDelete.'?hl=en');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'x='.$tokenX.'&s='.$idAlert.'&da=Delete');
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_REFERER, 'http://www.google.com/alerts/manage?hl=en');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Origin: http://www.google.com',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.3',
            'Cache-Control: max-age=0',
            'Connection: keep-alive	'
        ));

        $result=curl_exec($ch);
        $state=curl_getinfo($ch);

        if (!($state['http_code']==200)) {
            throw new GAlertException('Cannot delete the alert, bad response from server:'.json_encode($state));
        }

        return true;
    }

    /**
     * Returns an array with all the current alerts in the system
     * @return array with the keys: ('id', 'type', 'term', 'url');
     * @throws Exception when the server returns an incorrect response
     */
    public function getList()
    {
        $ch=$this->authentication();

        curl_setopt($ch, CURLOPT_URL, $this->urlManage);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, null);
        $result = curl_exec($ch);

        return $this->parseAlerts($result);
    }

    protected function authentication()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        curl_setopt($ch, CURLOPT_URL, $this->urlLogin);
        $data = $this->curl_exec_follow($ch);

        $formFields = $this->getFormFields($data);
        $formFields['Email']  = $this->user;
        $formFields['Passwd'] = $this->pass;
        unset($formFields['PersistentCookie']);

        $post_string = '';
        foreach($formFields as $key => $value)
        {
            $post_string .= $key . '=' . urlencode($value) . '&';
        }
        $post_string = substr($post_string, 0, -1);

        curl_setopt($ch, CURLOPT_URL, $this->urlAuth);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        $result = $this->curl_exec_follow($ch);

        return $ch;
    }

    protected function getFormFields($data)
    {
        if (preg_match('/(<form.*?id=.?gaia_loginform.*?<\/form>)/is', $data, $matches)) {
            $inputs = $this->getInputs($matches[1]);

            return $inputs;
        } else {
            throw new GAlertException('Cannot authenticate, bad response from server');
        }
    }

    protected function getInputs($form)
    {
        $inputs = array();

        $elements = preg_match_all('/(<input[^>]+>)/is', $form, $matches);

        if ($elements > 0) {
            for($i = 0; $i < $elements; $i++) {
                $el = preg_replace('/\s{2,}/', ' ', $matches[1][$i]);

                if (preg_match('/name=(?:["\'])?([^"\'\s]*)/i', $el, $name)) {
                    $name  = $name[1];
                    $value = '';

                    if (preg_match('/value=(?:["\'])?([^"\'\s]*)/i', $el, $value)) {
                        $value = $value[1];
                    }

                    $inputs[$name] = $value;
                }
            }
        }

        return $inputs;
    }

    protected function parseAlerts($data)
    {
        $regexp = "(?:<tr id=\"(.*)\" class=\"ACTIVE\">)(.*)(?:<\/tr>)";
        $regexp2= "(?:alert-type\" colspan=\"2\"><a href=\"[^\"]*\">)(.*)(?:<\/a>)";
        $regexp3= "Feed<\/a> <a href=\"([^\"]*)\">(.*)<\/a>";

        $res=array();
        if(preg_match_all("/$regexp/siU", $data, $matches))
        {
            $cnt=0;
            foreach($matches[2] as $rrow)
            {
                $itm['id']=$matches[1][$cnt];
                if (strpos($rrow, 'Feed')>0) {
                    $itm['type']='feed';
                } else {
                    $itm['type']='email';
                }

                if (preg_match_all("/$regexp2/siU", $rrow, $rcells))
                {
                    $itm['term']=$rcells[1][0];
                }

                if (preg_match_all("/$regexp3/siU", $rrow, $rcells))
                {
                    $itm['url']=$rcells[1][0];
                }

                $cnt++;
                $res[]=$itm;
            }
        }

        return $res;
    }

    protected function curl_exec_follow($ch) {
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,1);
        return curl_exec($ch);
    }
}
