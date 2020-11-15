#include <stdio.h>
//#include <unistd.h>
#include <stdlib.h>
#include <errno.h>
//#include "shell.c"
#include "defines.h"
#include "utils.c"

int
main()
{	
	if(INIT_CGI_ENV()!=0) _500();//PWD not exist or access denied.
	long length;
   	char* data = getenv("CONTENT_LENGTH");
	char cmd[MAX_COMMAND_LENGTH];
	if(!data || sscanf(data, "%ld", &length)!=1 || length>MAX_COMMAND_LENGTH)
		_400();
	fgets(cmd, length + sizeof(char), stdin);
	//printf("%s\n", cmd);
	char *buf = url_decode(cmd);
	if(strlen(buf) <= 5){
		_400();//no "data="
	}
	buf += 5 * sizeof(char);//strip "data="
	sprintf(buf, "%s 2>&1", buf);
	//printf(buf);	
	//shellcmd(buf);
	if(check_blacklist(buf, BLACKLIST_EXEC)){
		WARN("command in blacklist");
		_403();
	}
	//_200();
	HEADER("Content-type", "text/html");
	END_HEADERS();
	FILE *fcmd;
	if((fcmd = popen(buf, "r"))==NULL){
		printf("error:%s",strerror(errno));
	}else{
		unsigned char b[CHUNK_SIZE];
		int rc;
		while ((rc=fread(b, sizeof(unsigned char), CHUNK_SIZE, fcmd)) != 0){
			fwrite(b, sizeof(unsigned char), rc, stdout);
			fflush(stdout);
		}//read from pipe
		//pclose(fcmd);
		int exitcode = WEXITSTATUS(pclose(fcmd));
		if(exitcode)
			printf("***exit[%i]***\n", exitcode);
	}
	safe_free(buf);
	return 0;
	/*if((data = getenv("QUERY_STRING")))
		printf("%s", data);
	else
		printf("NOTHING");
	*/
	
//	printf("\n");
}
