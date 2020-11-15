.SUFFIXES: .o .c
.PHONY: clean all

GCC_FLAGS = -Wstrict-prototypes -Wpointer-arith -Wcast-align -Wcast-qual\
  -Wtraditional\
  -Wshadow\
  -Wconversion\
  -Waggregate-return\
  -Wmissing-prototypes\
  -Wnested-externs\
  -Wall \
  -Wundef -Wwrite-strings -Wredundant-decls -Winline

LDFLAGS =  -g
CFLAGS = -g -O2 -pipe -Wall -I.

CC = gcc 

OBJS = readfile savefile exec

all: $(OBJS)

exec: exec.c defines.h
	$(CC) -o $@ $^ $(LDFLAGS) $(CFLAGS)

savefile: savefile.c defines.h
	$(CC) -o $@ $^ $(LDFLAGS) $(CFLAGS)

readfile: readfile.c defines.h
	$(CC) -o $@ $^ $(LDFLAGS) $(CFLAGS)

clean:
	rm -f *.o $(OBJS)


