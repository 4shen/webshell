#include <stdio.h>
//#include <unistd.h>
#include <stdlib.h>
#include <string.h>

int
check_blacklist(char* input, const char* blacklist[]){
        char q[sizeof(input)];
        memcpy(q, input, sizeof(input));
        char* p = strtok(q, " ");
        int i;
        for(i=0;i<2;i++){
                //printf("%s->%s\n", blacklist[i], p);
                if(strcmp(blacklist[i], p)==0) return 1;
        };
        return 0;
}


//http://www.geekhideout.com/urlcode.shtml
/* Converts a hex character to its integer value */
char
from_hex(char ch) {
  return isdigit(ch) ? ch - '0' : tolower(ch) - 'a' + 10;
}


/* Returns a url-decoded version of str */
/* IMPORTANT: be sure to free() the returned string after use */
char*
url_decode(char *str) {
  char *pstr = str, *buf = malloc(strlen(str) + 1), *pbuf = buf;
  while (*pstr) {
    if (*pstr == '%') {
      if (pstr[1] && pstr[2]) {
        *pbuf++ = from_hex(pstr[1]) << 4 | from_hex(pstr[2]);
        pstr += 2;
      }
    } else if (*pstr == '+') { 
      *pbuf++ = ' ';
    } else {
      *pbuf++ = *pstr;
    }
    pstr++;
  }
  *pbuf = '\0';
  return buf;
}
