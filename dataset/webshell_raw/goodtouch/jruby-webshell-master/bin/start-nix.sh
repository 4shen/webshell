#!/usr/bin/env bash

case `uname` in
Darwin*)
  java -Xmx500m -Xss1024k -Djava.net.preferIPv4Stack=true -XstartOnFirstThread -jar vendor/jruby-complete.jar bin/webshell
  ;;
*)
  java -Xmx500m -Xss1024k -Djava.net.preferIPv4Stack=true -jar vendor/jruby-complete.jar bin/webshell
  ;;
esac
