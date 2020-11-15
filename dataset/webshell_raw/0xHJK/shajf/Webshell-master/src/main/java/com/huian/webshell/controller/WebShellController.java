package com.huian.webshell.controller;

import org.springframework.http.HttpRequest;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestMethod;

import javax.servlet.http.HttpServletRequest;

/**
 * Created by dell on 2017/3/18.
 */

@Controller
public class WebShellController {


    @RequestMapping(value="/**")
    public String webshell(HttpServletRequest request){

        String path = request.getServletPath();
       return path.substring(path.lastIndexOf('/')+1);

    }



}
