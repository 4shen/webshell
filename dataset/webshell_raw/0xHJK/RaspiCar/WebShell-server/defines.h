#define CHUNK_SIZE 1024
#define MAX_COMMAND_LENGTH 1024

#define DIE(mesg) perror(mesg), printf(mesg), exit(2)
#define WARN(mesg) perror(mesg)
#define safe_free(x) if(NULL!=(x)) free(x)

#define _HTTP_STATUS(msg) printf("Status: %s\n\n", msg)
#define _400() _HTTP_STATUS("400 Bad Request"), exit(1)
#define _403() _HTTP_STATUS("403 No Way"), exit(1)
#define _404() _HTTP_STATUS("404 Not Here"), exit(1)
#define _500() _HTTP_STATUS("500 Server Sick"), exit(1)

#define HEADER_L(h, c) printf("%s: %ld\n", h, c)
#define HEADER(h, c) printf("%s: %s\n", h, c)
#define END_HEADERS() printf("\n")

#define INIT_CGI_ENV() chdir(getenv("HTTP_X_PWD"))
const char* BLACKLIST_EXEC[] = {
"su",
"sudo"
"/usr/bin/su",
"/usr/bin/sudo",
"/usr/libexec/sudo"
};

const char* BLACKLIST_PREFIX[] = {
"/usr/bin",
"/usr/bin64",
"/sbin",
"/bin",
"/usr/libexec"
};
