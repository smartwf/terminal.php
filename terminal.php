<?php

/**
 * Terminal.php - Terminal Emulator for PHP
 *
 * @package  Terminal.php
 * @author   SmartWF <hi@smartwf.ir>
 */

/* Choose a random key Like ('Mmbuge8maD5VAUMc') for Security */
define('KEY', 'Mmbuge8maD5VAUMc');

if (!isset($_GET['key']) && $_GET['key'] != KEY)
    header('location: /');

class CustomCommands{

    /***************************************************************
     *                 Add Your Custom Command Here                *
     ***************************************************************
     *    note 1: Function Name is Command and return is Result    *
     *    note 2: $a is array of arguments                         *
     * *************************************************************/

    public static function hi($a){
        return 'Hi '.implode(' ', $a);
    }

    public static function md5($a){
        $input = implode(' ', $a);
        if ($input)
            return md5($input);
        else
            return 'write something, example:<br>md5 test';
    }

    public static function developer(){
        return 'SmartWF<br><a href="https://github.com/smartwf" target="_blank">github</a> &nbsp; &nbsp; <a href="mailto:hi@smartwf.ir" target="_blank">mail</a> &nbsp; &nbsp; <a href="http://twitter.com/smartwf" target="_blank">twitter</a>';
    }
}

class TerminalPHP{

    /* These Commands Doesn't Execute */
    private $blocked_commands = [
      /*'mkdir',
        'rm',
        'git',
        'wget',
        'curl',
        'chmod',
        'rename',
        'mv',
        'cp'*/
    ];

    /**
     * initialize Class
     * @param $path string default path to start
     */
    public function __construct($path = ''){
        $this->_cd($path);
    }

    /**
     * Execute Shell Command
     * @param $cmd string command
     * @return string
     */
    private function shell($cmd){
        return trim(shell_exec($cmd));
    }

    /**
     * Check Command Exists
     * @param $command string command to check
     * @return bool
     */
    private function commandExists($command){
        if ($this->shell('command -v '.$command))
            return true;
        return false;
    }

    /**
     * Run Commands as Class method
     * @param $cmd string command
     * @param $arg array arguments
     * @return string
     */
    public function __call($cmd, $arg){
        return $this->runCommand($cmd.(isset($arg[0]) ? ' '.$arg[0] : ''));
    }

    /**
     * Run Command in Terminal
     * @param $command string command to run
     * @return string
     */
    public function runCommand($command){
        $cmd = explode(' ', $command)[0];
        $arg = count(explode(' ', $command)) > 1 ? implode(' ', array_slice(explode(' ', $command), 1)) : '';

        if (array_search($cmd, $this->getLocalCommands()) !== false){
            $lcmd = '_'.$cmd;
            return $this->$lcmd($arg);
        }
        else if (array_search($cmd, $this->blocked_commands) !== false)
            return 'terminal.php: Permission denied';
        else if ($this->commandExists($cmd))
            return trim(shell_exec($command));
        else
            return 'terminal.php: command not found: '.$cmd;
    }

    /**
     * Normalize text for show in html
     * @param $input string input text
     * @return string
     */
    public function normalizeHtml($input){
        return str_replace(['<', '>', "\n", "\t", ' '], ['&lt;', '&gt;', '<br>', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', '&nbsp;'], $input);
    }

    /**
     * Array of Local Commands
     * @return array
     */
    private function getLocalCommands(){
        $commands = array_filter(get_class_methods($this), function ($i){ return ($i[0] == '_' && $i[1] != '_') ? true : false; });
        foreach ($commands as $i => $command)
            $commands[$i] = substr($command, 1);
        return $commands;
    }




    /************************************************************/
    /*                      Local Commands                      */
    /*                                                          */
    /*             note: command must start with '_'            */
    /************************************************************/

    /**
     * Change Directory Command
     * @param $path string patch to change
     * @return string
     */
    private function _cd($path){
        if ($path)
            chdir($path);
        return '';
    }

    /**
     * Current Working Directory Command
     * @return string
     */
    private function _pwd(){
        return getcwd();
    }

    /**
     * Ping Command
     * @return string
     */
    private function _ping($a){
        if (strpos($a, '-c ') !== false)
            return trim(shell_exec('ping '.$a));

        return trim(shell_exec('ping -c 4 '.$a));
    }

}


/* Check if Request is Ajax */
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && isset($_POST['command'])) {
    $command = explode(' ', $_REQUEST['command'])[0];
    $arguments = array_slice(explode(' ', $_REQUEST['command']), 1);
    $path = isset($_REQUEST['path']) ? $_REQUEST['path'] : '' ;

    $terminal = new TerminalPHP($path);

    if (array_search($command, get_class_methods('CustomCommands')) !== false)
        print_r(json_encode((object) ['result' => CustomCommands::$command($arguments), 'path' => $terminal->pwd()]));
    else
        print_r(json_encode((object) ['result' => $terminal->normalizeHtml($terminal->runCommand($_REQUEST['command'])), 'path' => $terminal->pwd()]));
}

else{
    $terminal = new TerminalPHP();
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Terminal.php</title>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <link href="https://cdn.rawgit.com/rastikerdar/vazir-code-font/v1.1.2/dist/font-face.css" rel="stylesheet" type="text/css" />
    <style>
      :root{
          --background-url: url(https://images.unsplash.com/photo-1485470733090-0aae1788d5af?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&w=1920);
          --font: 'Vazir Code', 'Vazir Code Hack';
          --font-size: 16px;
          --primary-color: #101010;
          --color-scheme-1: #55c2f9;
          --color-scheme-2: #ff5c57;
          --color-scheme-3: #5af68d;
          --scrollbar-color: #181818;
          --title-color: white;
          --blink-color: #979797;
          --blink: '|';
          --separator: '--->';
      }
      ::-webkit-scrollbar { width: 7px; }
      ::-webkit-scrollbar-track {  background: rgba(0,0,0,0); }
      ::-webkit-scrollbar-thumb { background: var(--scrollbar-color); border-radius: 5px; }
      *{ font-family: var(--font);}
      body{ background: var(--background-url) center no-repeat; background-size: cover; height: 100vh; width: 100vw; margin: 0; padding: 0; background-attachment: fixed; overflow: hidden; }
      a{ color: #29a9ff; }
      terminal{ display: block; width: 80vw;  height: 80vh; position: relative; margin: 7rem auto; background: inherit; border-radius: 10px; max-width: 70rem; overflow: hidden; }
      terminal::before,
      terminal::after{ content: ''; position: absolute; left: 0; top: 0; height: 100%; width: 100%; border-radius: 10px; }
      terminal::before{ background: inherit; filter: blur(.5rem); }
      terminal::after{ background: var(--primary-color); opacity: .75; }
      terminal header{ position: absolute; width: 100%; height: 45px; background: var(--primary-color); z-index: 1; border-radius: 10px 10px 0 0; user-select: none; }
      terminal header title{ display: block; position: absolute; left: 0; top: 0; width: 100%; height: 100%; text-align: center; color: var(--title-color); line-height: 45px; opacity: .8; z-index: -1; }
      terminal header buttons{ padding: 1rem; display: block; }
      terminal header buttons *{ display: inline-block; width: 15px; height: 15px; background: rgba(255,255,255,.1); border-radius: 50%; margin-right: 5px; cursor: pointer; }
      terminal header buttons close{ background: #fc615d; }
      terminal header buttons maximize{ background: #fdbc40; }
      terminal header buttons minimize{ background: #34c749; }
      terminal content{ position: absolute; left: 1.5%; top: 60px; width: 98%; height: 92%; z-index: 1; overflow-x: hidden; overflow-y: auto; color: #ececec; font-size: var(--font-size); }
      terminal content line{ display: block; }
      terminal content path{ color: var(--color-scheme-1); }
      terminal content sp{ color: var(--color-scheme-2); letter-spacing: -6px; margin-right: 5px;}
      terminal content sp::before{ content: var(--separator);}
      terminal content cm{ color: var(--color-scheme-3); }
      terminal content code{ display: inline; margin: 0; white-space: unset;}
      terminal content bl{ color: var(--blink-color); position: relative; top: -2px; }
      terminal content bl::before{ content: var(--blink); animation: blink 2s steps(1) infinite;}
      footer{ position: absolute; width: 100%; left: 0; bottom: 20px; color: white; text-align: center; font-size: 12px; }
      footer a{ text-decoration: none; color: #fdbc40; }
      @keyframes blink { 0% { opacity: 1} 50% { opacity: 0} 100% { opacity: 1} }
    </style>
    <script type="text/javascript">
        var path = '<?php echo $terminal->pwd();?>';
        var command = '';
        var command_history = [];
        var history_index = 0;
        var suggest = false;
        var blink_position = 0;

        $(document).keydown(function(e) {
            var keyCode = typeof e.which === "number" ? e.which : e.keyCode;

            if (keyCode === 32
                || keyCode === 222
                || keyCode === 220
                || (
                    (keyCode >= 45 && keyCode <= 195)
                    && !(keyCode >= 112 && keyCode <= 123)
                    && keyCode != 46
                    && keyCode != 91
                    && keyCode != 93
                    && keyCode != 144
                    && keyCode != 145
                    && keyCode != 45
                )
            ){
                type(e.key);
                history_index = 0;
                suggest = false;
                $('terminal content').scrollTop($('terminal content').prop("scrollHeight"));
            }

            /* Tab, Backspace and Delete key */
            else if (keyCode === 8 || keyCode === 9 || keyCode === 46) {
                e.preventDefault();
                if (command !== ''){
                    if (keyCode === 8)
                        backSpace();
                    else if (keyCode === 46)
                        reverseBackSpace();
                }
            }

            /* Ctrl + C */
            else if (e.ctrlKey === true && keyCode===67){
                addToHistory(command);
                endLine();
                newLine();
                reset();
            }

            /* Enter */
            else if (keyCode === 13){

                if (command.toLowerCase().split(' ')[0] in commands)
                    commands[command.toLowerCase().split(' ')[0]](command.split(' ').slice(1));
                else if(command.length !== 0)
                    $.ajax({
                        type: 'POST',
                        async: false,
                        data: {command: command, path: path},
                        cache: false,
                        success: function( response ){
                            response = $.parseJSON(response);
                            path = response.path;
                            $('terminal content').append('<line>'+response.result+'</line>');
                        }
                    });

                endLine();
                addToHistory(command);
                newLine();
                reset();
            }

            /* Home, End, Left and Right (change blink position) */
            else if ((keyCode === 35 || keyCode === 36 || keyCode === 37 || keyCode === 39) && command !== ''){
                e.preventDefault();
                $('line.current bl').remove();

                if (keyCode === 35)
                    blink_position = 0;

                if (keyCode === 36)
                    blink_position = command.length*-1;

                if (keyCode === 37 && command.length !== Math.abs(blink_position))
                    blink_position--;

                if (keyCode === 39 && blink_position !== 0)
                    blink_position++;

                printTypedCommand();
                normalizeHtml();
            }

            /* Up and Down (suggest command from history)*/
            else if ((keyCode === 38 || keyCode === 40) && (command === '' || suggest)){
                e.preventDefault();
                if (keyCode === 38
                    && command_history.length
                    && command_history.length >= history_index*-1+1) {

                    history_index--;
                    command = command_history[command_history.length+history_index];
                    printTypedCommand();
                    normalizeHtml();
                    suggest = true;
                }
                else if (keyCode === 40
                    && command_history.length
                    && command_history.length >= history_index*-1
                    && history_index !== 0) {

                    history_index++;
                    command = command_history[command_history.length+history_index];
                    printTypedCommand();
                    normalizeHtml();
                    suggest = (history_index === 0) ? false : true;
                }
            }
        });

        function reset() {
            command = '';
            history_index = 0;
            blink_position = 0;
            suggest = false;
        }
        function endLine() {
            $('line.current bl').remove();
            $('line.current').removeClass('current');
        }
        function newLine() {
            $('terminal content').append('<line class="current"><path>'+path+'</path> <sp></sp> <t><bl></bl></t></line>');
        }
        function addToHistory(command) {
            if ((command.length && command_history.length === 0) || (command_history[command_history.length-1] !== command))
                command_history[command_history.length] = command;
        }
        function normalizeHtml() {
            let res = $('line.current t').html();
            let nres = res.split(' ').length == 1 ? '<cm>'+res+'</cm>' : '<cm>'+res.split(' ')[0]+'</cm> <code>'+res.split(' ').slice(1).join(' ').replace(/</g, '&lt;').replace(/>/g, '&gt;')+'</code>';

            $('line.current t').html(nres.replace('&lt;bl&gt;&lt;/bl&gt;', '<bl></bl>'));
        }
        function printTypedCommand() {
            let part1 = command.substr(0, command.length + blink_position);
            let part2 = command.substr(command.length + blink_position);

            $('line.current t').html(part1 + '<bl></bl>' + part2);
        }
        function type(t) {
            let part1 = command.substr(0, command.length + blink_position);
            let part2 = command.substr(command.length + blink_position);
            command = part1+t+part2;

            printTypedCommand();
            normalizeHtml();
        }
        function backSpace() {
            let part1 = command.substr(0, command.length + blink_position);
            let part2 = command.substr(command.length + blink_position);
            command = part1.substr(0, part1.length-1)+part2;

            printTypedCommand();
            normalizeHtml();
        }
        function reverseBackSpace() {
            let part1 = command.substr(0, command.length + blink_position);
            let part2 = command.substr(command.length + blink_position);
            command = part1+part2.substr(1);

            if (blink_position !== 0)
                blink_position++;

            printTypedCommand();
            normalizeHtml();
        }


        /**********************************************************/
        /*                     Local Commands                     */
        /**********************************************************/

        var commands = {
            'clear' : clear,
            'history': history,
        };

        function clear(){
            $('terminal content').html('');
        }

        function history(arg){
            var res = [];
            let start_from = arg.length ? Number.isInteger(Number(arg[0])) ? Number(arg[0]) : 0 : 0;

            if (start_from != 0 && start_from <= command_history.length)
                for (var i = command_history.length-start_from; i < command_history.length; i++) { res[res.length] = (i+1)+' &nbsp;'+command_history[i]; }
            else
                command_history.forEach(function(item, index) { res[res.length] = (index+1)+' &nbsp;'+item; });

            $('terminal content').append('<line>'+res.join('<br>')+'</line>');
      }

    </script>
  </head>
  <body>
    <terminal>
      <header>
        <buttons>
          <close title="close"></close>
          <maximize title="maximize"></maximize>
          <minimize title="minimize"></minimize>
        </buttons>
        <title>Terminal.php &nbsp; <?php echo '('.($terminal->whoami() ? $terminal->whoami() : '').($terminal->whoami() && $terminal->hostname() ? '@'.$terminal->hostname() : '').')';?></title>
      </header>
      <content>
        <line class="current"><path><?php echo $terminal->pwd();?></path> <sp></sp> <t><bl></bl></t></line>
      </content>
    </terminal>
    <footer>Coded by <a href="https://github.com/smartwf">SmartWF</a></footer>
  </body>
</html>
<?php } ?>
