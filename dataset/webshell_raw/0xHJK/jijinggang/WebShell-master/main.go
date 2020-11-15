package main

import (
	"flag"
	"fmt"
	"github.com/jijinggang/go-websocket"
	"github.com/jijinggang/goutil"
	"html/template"
	"io"
	"io/ioutil"
	"net/http"
	"os"
	"os/exec"
	"runtime"
	"strconv"
	"strings"
	"time"
)

var tmpl, _ = template.New("main").Parse(TMPL_MAIN)

func showCmdListPage(w http.ResponseWriter, req *http.Request) {
	tmpl.Execute(w, _config.Cmds)
}

func showCmdResultInitPage(w http.ResponseWriter, req *http.Request) {
	id := req.FormValue("id")
	html := strings.Replace(_html, "{id}", id, -1)
	io.WriteString(w, html)
}

func writeString(w io.Writer, str string) {
	w.Write([]byte(str))
}

func exec_cmd(id int, w *websocket.Conn) {
	cmdCfg := &_config.Cmds[id]
	if cmdCfg.Running {
		writeString(w, "The script is running, please waitting .......")
		return
	}
	cmdCfg.Running = true
	strCmd := cmdCfg.Script
	var cmd *exec.Cmd
	if runtime.GOOS == "windows" {
		content, err := ioutil.ReadFile(cmdCfg.Script)
		if err != nil {
			writeString(w, err.Error())
			return
		}
		strCmd = cmdCfg.Script + ".tmp" + strconv.Itoa(id) + ".bat"
		goutil.WriteStringFile(strCmd, "@echo off \r\n chcp 65001 \r\n"+string(content))
		defer os.Remove(strCmd)
	}
	cmd = exec.Command(strCmd)
	cmd.Env = os.Environ()
	cmd.Stdout = w

	path := goutil.GetPath(cmdCfg.Script)
	cmd.Dir = path

	err := cmd.Start()
	if err != nil {
		writeString(w, err.Error())
		return
	}

	cmd.Wait()
	cmdCfg.Running = false
	cmdCfg.LastRunTime = time.Now()
	writeString(w, "\n---------------------\nRUN OVER.......................")
	writeString(w, "\nDownload Url:\n"+cmdCfg.Url)

}

func execAndRefreshCmdResult(ws *websocket.Conn) {
	req := ws.Request()
	id, _ := strconv.Atoi(req.FormValue("id"))
	if id >= len(_config.Cmds) {
		writeString(ws, "Invalid Command.")
		return
	}

	//ws.SetWriteDeadline(time.Now().Add(20 * time.Second))
	exec_cmd(id, ws)
}

type Cmd struct {
	Text        string
	Script      string
	Url         string
	Running     bool
	LastRunTime time.Time
}

type Config struct {
	WWWRoot string
	Port    int
	Cmds    []Cmd
}

var _html string
var _config Config
var port int

func main() {
	flag.Parse()
	goutil.ParseJsonFile(&_config, "config.json")
	port = _config.Port
	_html = strings.Replace(HTML_EXEC, "{port}", strconv.Itoa(port), -1)
	http.HandleFunc("/run", showCmdListPage)
	http.HandleFunc("/run/cmd", showCmdResultInitPage)
	http.Handle("/run/exec", websocket.Handler(execAndRefreshCmdResult))
	http.Handle("/", http.FileServer(http.Dir(_config.WWWRoot))) //use fileserver directly
	fmt.Printf("http://localhost:%d/run\n", port)
	err := http.ListenAndServe(fmt.Sprintf(":%d", port), nil)
	if err != nil {
		panic("ListenAndServe: " + err.Error())
	}
}

const HTML_EXEC = `
<html>
<head>
<script type="text/javascript">
var path;
var ws;
function init() {
   console.log("init");
   if (ws != null) {
     ws.close();
     ws = null;
   }
   var div = document.getElementById("msg");
   var host = window.location.host;
   div.innerText =  "\n" + div.innerText;
   ws = new WebSocket("ws://" + host + "/run/exec?id={id}");
   ws.binaryType ="string";
   ws.onopen = function () {
    //div.innerText = "opened\n" + div.innerText;
	//ws.send("ok");
   };
   ws.onmessage = function (e) {
      div.innerText = div.innerText + e.data;
   };
   ws.onclose = function (e) {
     // div.innerText = div.innerText + "closed";
   };
   //div.innerText = "init\n" + div.innerText;
};
</script>
<body onLoad="init();"/>
<div id="msg"></div>
</html>
`

const TMPL_MAIN = `
<html>
<head>
</head>
<body>
<table border="0" cellspacing="8">
	<thead><tr><th>Name</th><th></th><th>Last run time</th></tr></thead>
	{{with .}}
	{{range $k, $v := .}}
	<tr>
		<td><a href="/run/cmd?id={{$k}}" target="_blank" onclick="return confirm('Do you really run this script?');">{{$v.Text}}</td>
		<td><a href="{{$v.Url}}">Download</td>
		{{with $v.LastRunTime}}
		<td>{{.}}</td>
		{{end}}
	</tr>
	{{end}}
	{{end}}
</table>
</body>
</html>
`
