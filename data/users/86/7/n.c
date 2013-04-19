#include <stdio.h>
#include <stdlib.h>
#include <string.h>


int main(void)
{
	char buf[51];
	int amnt;

	fscanf(stdin, "%d\n", &amnt);

	while(amnt-- > 0)
	{
		memset(buf, 0, sizeof(buf));
		gets(buf);
		
		int i=0,j=0,k=0;

		while(buf[i] != 0 && buf[i] == ' ')
			i++;

		while(buf[i] != 0 && buf[i] == '0')
			i++;

		j = i;

		if(buf[i] == 0)
		{
			printf("invalid input\n");
			continue;
		}

		while(buf[i] != 0 && buf[i]	>= '0' && buf[i] <= '9')
			i++;

		if(buf[i] != 0)
			k = i;

		while(buf[i] != 0 && buf[i] == ' ')
			i++;

		if(buf[i] != 0)
		{
			printf("invalid input\n");
			continue;
		}

		if(k > 0)
			printf("%.*s\n", k-j, &buf[j]);
		else
			printf("%s\n", &buf[j]);
	}
}
