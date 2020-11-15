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
	if(strlen(path) <= 5){
        	_400();//no "data="
        }
        path += 5 * sizeof(char);//strip "data="
	path = url_decode(path);
	//printf(path);
	if(check_blacklist(path, BLACKLIST_PREFIX))
		_403();//not working
	struct stat s;
	if(stat(path, &s)==0){
		if (s.st_mode&S_IFDIR) _403();
	}
	FILE *f;
	if((f=fopen(path, "rb")) == NULL)
		_404();
	else{	
		HEADER("Content-type","application/octet-stream");
		fseek(f, 0L, SEEK_END);
		long len = ftell(f);
		HEADER_L("Content-Length", len);
		END_HEADERS();
		rewind(f);			
		unsigned char b[CHUNK_SIZE];
		int rc;
		while ((rc=fread(b, sizeof(unsigned char), CHUNK_SIZE, f)) != 0){
			fwrite(b, sizeof(unsigned char), rc, stdout);
		}
		fclose(f);
	}
	free(path);
	return 0;
;}

