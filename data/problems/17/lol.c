#include <stdio.h>
#include <string.h>

char s[100000];

void strip(char *s) {
	int l=strlen(s);
	while(l && (s[l-1]=='\n' || s[l-1]=='\r')) l--;
	s[l]=0;
}

int empty(char *s) { return strlen(s)==0; }

int main() {
	int i;
	/* read lines:
	   1: number of units
	   100: the units
	   1: number of tests
	   1000: the tests */
	for(i=0;i<1+100+1+1000;i++) {
		if(!gets(s)) goto fail;
		strip(s);
		if(empty(s)) goto fail;
	}
	/* read one blank line? */
	if(!gets(s)) goto fail;
	strip(s);
	if(!empty(s)) goto fail;
	/* read 1000 cases */
	for(i=0;i<1000;i++) {
		if(!gets(s)) goto fail;
		strip(s);
		if(empty(s)) goto fail;
	}
	/* now we should be at eof */
	if(gets(s)) goto fail;
	puts("1");
	return 0;
fail:
	puts("-1");
	return 0;
}
