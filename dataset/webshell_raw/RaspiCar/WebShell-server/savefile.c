#include <stdio.h>
#include <stdlib.h>
#include <sys/types.h>
#include <sys/stat.h>
#include "defines.h"
#include "utils.c"

int
main()
{			
	if(INIT_CGI_ENV()!=0) _500();//PWD not exist or access denied.
//	_200();
	char *path = getenv("QUERY_STRING");
	/*if(strlen(path) <= 5){
                _400();//no "data="
        }
        path += 5 * sizeof(char);//strip "data="*/
	path = url_decode(path);
	if(check_blacklist(path, BLACKLIST_PREFIX))
		_403();//not working
	struct stat s;
	if(stat(path, &s)==0){
		if (s.st_mode&S_IFDIR) _403();
	}
	long length;
  	char* data = getenv("CONTENT_LENGTH");
	if(!data || sscanf(data, "%ld", &length)!=1)
		_400();
	FILE *f;
	if((f=fopen(path, "wb")) == NULL)
		_404();
	else{	
		HEADER("Content-type","text/html");
		END_HEADERS();	
		unsigned char b[CHUNK_SIZE];
		int rc;
		while ((rc=fread(b, sizeof(unsigned char), CHUNK_SIZE, stdin)) != 0){
			fwrite(b, sizeof(unsigned char), rc, f);
		}
		fclose(f);
	}
	free(path);
	return 0;
}

