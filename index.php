<?php
  
if( !class_exists("ServerStatusException") ){
    class ServerStatusException extends Exception { }    
}

if( !class_exists("ServerStatus") ){

  class ServerStatus {

    public string $os;

    function __construct() {
      $this->os = PHP_OS;
    }

    public function init(){
      try {
        if(!empty($_REQUEST['ajax'])) {
          $content = $this->doAjax();
          echo $content;
        }
        else {
          $content = '';
          $content .= $this->uptimeContent();
          $content .= $this->diskSpaceContent();
          $content .= $this->cpuTempContent();
          $content .= $this->topContent();
          $this->output($content);
        }
      } catch(ServerStatusException $e){
        die("Error: {$e->getMessage()}\n");
      } 
    }

    public function uptimeContent() {
      $content = '';
      //uptime
      $uptime = $this->uptimeOutput();
      if(!empty($uptime)) {
        $content =  '<h2>Uptime</h2>';
        $content .= '<pre id="uptime-content">'.$uptime.'</pre>';
      }
      return $content;
    }

    private function uptimeOutput() {
      $uptime = '';
      $uptime = shell_exec('uptime');
      return $uptime;
    }

    public function diskSpaceContent() {
      $content = '';
      //cpu temp
      $disc_space = $this->diskSpaceOutput();
      if(!empty($disc_space)) {
        $content =  '<h2>Disk space</h2>';
        $content .= '<pre id="disk-space-content">'.$disc_space.'</pre>';
      }
      return $content;
    }

    private function diskSpaceOutput() {
      $disc_space = '';
      $disc_space = shell_exec('df -h -T');
      return $disc_space;
    }

    public function cpuTempContent() {
      $content = '';
      //disc space
      $cpu_temp = $this->cpuTempOutput();
      if(!empty($cpu_temp)) {
        $content =  '<h2>CPU Temp</h2>';
        $content .= '<pre id="cpu-temp-content">'.$cpu_temp.'</pre>';
      }
      return $content;
    }

    private function cpuTempOutput() {
      $cpu_temp = '';
      switch($this->os) {
        case 'FreeBSD':
          $cpu_temp = shell_exec('sysctl dev.cpu | grep temperature');
          break;
        case 'Linux':
          $cpu_temp = shell_exec("paste <(cat /sys/class/thermal/thermal_zone*/type) <(cat /sys/class/thermal/thermal_zone*/temp) | column -s $'\t' -t | sed 's/\(.\)..$/.\1°C/'");
          break;
      }
      return $cpu_temp;
    }

    public function topContent() {
      $content = '';
      //top
      $top = $this->topOutput();
      if(!empty($top)) {
        $content =  '<h2>Processes</h2>';
        $content .= '<pre id="top-content">'.$top.'</pre>';
      }
      return $content;
    }

    private function topOutput($n = 20) {
      $top = '';
      switch($this->os) {
        case 'FreeBSD':
          $top = shell_exec('top -n '.$n);
          break;
        case 'Linux':
          ob_start();
          passthru('/usr/bin/top -b -n 1|head -n '.($n+7));
          $top = ob_get_clean();
          ob_clean();
          break;
      }
      return $top;
    }

    function doAjax() {
      $content = '';
      if(!empty($_REQUEST['ajax'])) {
        switch ($_REQUEST['param']) {
          case 'cpu_temp':
            $content = $this->cpuTempOutput();
            break;

          case 'top':
            $content = $this->topOutput();
            break;
          
          default:
            // code...
            break;
        }
      }
      return $content;
    }

    public function output($content) {
      $title = 'Server Status';
      $styles = '<style>body {background: #f5f5f5;color: #3c434a;font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;font-size: 13px;line-height: 1.4;}</style>';
      $script = '<script type="text/javascript">
      (function() {
        function callAjax(url, elemId) {
            var xmlhttp;
            // compatible with IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function(){
                if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
                    updateAjaxContent(xmlhttp.responseText, elemId);
                }
            }
            xmlhttp.open("GET", url, true);
            xmlhttp.send();
        }
        function updateAjaxContent(responseText, elemId) {
          if (responseText) {
            var container = document.getElementById(elemId);
            container.innerHTML = responseText;
          }
        }
        var element = document.getElementById("cpu-temp-content");
        if(typeof(element) != "undefined" && element != null){
          //callAjax(window.location.href + "?ajax=1&param=top", "top-content");
          var intervalTop = setInterval(callAjax, 9000, window.location.href + "?ajax=1&param=cpu_temp", "cpu-temp-content");
        }
        var element = document.getElementById("top-content");
        if(typeof(element) != "undefined" && element != null){
          //callAjax(window.location.href + "?ajax=1&param=top", "top-content");
          var intervalTop = setInterval(callAjax, 11000, window.location.href + "?ajax=1&param=top", "top-content");
        }
      })();
      </script>';
      echo sprintf('<!DOCTYPE html><html><head><title>%s</title>%s</head><body><div id="container">%s</div>%s</body></html>', $title, $styles, $content, $script);
    }

  }

  (new ServerStatus())->init();
}