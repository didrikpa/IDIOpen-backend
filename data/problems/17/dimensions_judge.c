#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <ctype.h>
#include <math.h>

#define MAX 1000
#define MAXS 16
#define MAXU 6
#define MAXL 131072

#define EPS 1e-6

char unit[MAX][MAXS];
double val[MAX];
int ex[MAX][MAXU]; /* m, s, kg, A, K, cd */
int n;
int cases;

char sol[1000][10000];

void add(char *s,double v,int a,int b,int c,int d,int e,int f) {
	strcpy(unit[n],s);
	val[n]=v;
	ex[n][0]=a;
	ex[n][1]=b;
	ex[n][2]=c;
	ex[n][3]=d;
	ex[n][4]=e;
	ex[n++][5]=f;
}

/* initialize standard units */
void init() {
	n=0;
	add("m", 1.0, 1, 0, 0, 0, 0, 0);
	add("s", 1.0, 0, 1, 0, 0, 0, 0);
	add("kg",1.0, 0, 0, 1, 0, 0, 0);
	add("A", 1.0, 0, 0, 0, 1, 0, 0);
	add("K", 1.0, 0, 0, 0, 0, 1, 0);
	add("cd",1.0, 0, 0, 0, 0, 0, 1);
}

/* read a token */
int eat(char *s,int *p,char *t) {
	while(isspace(s[*p])) (*p)++;
	if(!s[*p]) return 0;
	while(s[*p] && !isspace(s[*p])) *(t++)=s[(*p)++];
	*t=0;
	return 1;
}

/* extract and remove power from string */
void getmul(char *s,int *mul) {
	for(;*s;s++) if(*s=='^') break;
	if(*s) {
		*(s++)=0;
		*mul=0;
		while(*s) *mul=*mul*10+*(s++)-48;
	} else *mul=1;
}

/* given unit, find id (warning, stupid algorithm) */
int getid(char *s) {
	int i;
	for(i=0;i<n;i++) if(!strcmp(s,unit[i])) return i;
	printf("alarm, couldn't find %s\n",s);
	return -1;
}

void readdef() {
	static char s[MAXL+2],t[MAXL+2],u[MAXL+2];
	double v;
	int m,p,e[MAXU],i,neg,mul,id;
	fgets(s,MAXL,stdin);
	sscanf(s,"%d",&m);
	while(m--) {
		fgets(s,MAXL,stdin);
		p=0;
		/* read name */
		eat(s,&p,t);
		/* read = */
		eat(s,&p,u);
		/* read number */
		eat(s,&p,u);
		sscanf(u,"%lf",&v);
		/* read units EOL */
		for(i=0;i<MAXU;i++) e[i]=0;
		neg=1;
		while(eat(s,&p,u)) {
			if(!strcmp(u,"/")) { neg=-1; continue; }
			getmul(u,&mul);
			id=getid(u);
			for(i=0;i<MAXU;i++) e[i]+=ex[id][i]*neg*mul;
			v*=pow(val[id],mul*neg);
		}
		strcpy(unit[n],t);
		for(i=0;i<MAXU;i++) ex[n][i]=e[i];
		val[n++]=v;
	}
/*	for(i=0;i<n;i++) {
		printf("%s %f",unit[i],val[i]);
		for(id=0;id<MAXU;id++) printf(" %d",ex[i][id]);
		printf("\n");
	}*/
}

/* store pointers to the start of each token in **tp */
void split(char *s,char *t,char **tp,int *tn) {
	int p=0,q=0;
	*tn=0;
	while(eat(s,&p,t+q)) {
		tp[(*tn)++]=t+q;
		q+=strlen(t+q)+1;
	}
}

/* check if token is a number in float format */
int isnumber(char *t) {
	char *u;
	strtod(t,&u);
	return *u==0;
}

void parse(char **tp,int start,int end,double *v,int *e) {
	double w;
	int neg=1,mul,id,i;
	for(i=0;i<MAXU;i++) e[i]=0;
	sscanf(tp[start++],"%lf",&w);
	while(start<end) {
		if(!strcmp(tp[start],"/")) { start++; neg=-1; continue; }
		getmul(tp[start],&mul);
		id=getid(tp[start++]);
		w*=pow(val[id],mul*neg);
		for(i=0;i<MAXU;i++) e[i]+=ex[id][i]*mul*neg;
	}
	*v=w;
}

void readcases() {
	static char s[MAXL+2],t[MAXL+2];
	static char *tp[1000];
	int T,tn,i,op,hasneg,caseno=0;
	double v[2];
	int e[2][MAXU];
	fgets(s,MAXL,stdin);
	sscanf(s,"%d",&T);
	cases=T;
	while(T--) {
		fgets(s,MAXL,stdin);
		split(s,t,tp,&tn);
		sol[caseno][0]=0;
		/* find operator */
		for(op=0;op<tn-1;op++) {
			if(!strcmp(tp[op],"*") || !strcmp(tp[op],"+") || !strcmp(tp[op],"-") || !strcmp(tp[op],"/")) {
				if(isnumber(tp[op+1])) goto two;
			}
		}
		parse(tp,0,tn,&v[0],e[0]);
		goto output;
	two:;
		parse(tp,0,op,&v[0],e[0]);
		parse(tp,op+1,tn,&v[1],e[1]);
		if(!strcmp(tp[op],"*")) {
			for(i=0;i<MAXU;i++) e[0][i]+=e[1][i];
			v[0]*=v[1];
		} else if(!strcmp(tp[op],"/")) {
			for(i=0;i<MAXU;i++) e[0][i]-=e[1][i];
			v[0]/=v[1];
		} else {
			for(i=0;i<MAXU;i++) if(e[0][i]!=e[1][i]) {
				strcat(sol[caseno]+strlen(sol[caseno]),"Incompatible");
				goto done;
			}
			v[0]+=v[1]*(!strcmp(tp[op],"+")?1:-1);
		}
	output:
		sprintf(sol[caseno]+strlen(sol[caseno]),"%.15e",(double)v[0]);
		for(hasneg=i=0;i<MAXU;i++) if(e[0][i]>0) {
			sprintf(sol[caseno]+strlen(sol[caseno])," %s",unit[i]);
			if(e[0][i]>1) sprintf(sol[caseno]+strlen(sol[caseno]),"^%d",e[0][i]);
		} else if(e[0][i]<0) hasneg=1;
		if(hasneg) {
			sprintf(sol[caseno]+strlen(sol[caseno])," /");
			for(i=0;i<MAXU;i++) if(e[0][i]<0) {
				sprintf(sol[caseno]+strlen(sol[caseno])," %s",unit[i]);
				if(e[0][i]<-1) sprintf(sol[caseno]+strlen(sol[caseno]),"^%d",-e[0][i]);
			}
		}
		strcat(sol[caseno]+strlen(sol[caseno])," ");
	done:;
		caseno++;
	}
	fgets(s,MAXL,stdin);
}

void sorter(char **z,int start,int end) {
	int i,j;
	char *p;
	for(i=start;i<end;i++) for(j=start;j<end-1;j++) if(strcmp(z[j],z[j+1])>0) {
		p=z[j]; z[j]=z[j+1]; z[j+1]=p;
	}
}

int compare(int caseno) {
	static char s[MAXL+2],t1[MAXL+2];
	static char *tp1[1000];
	static char t2[MAXL+2];
	static char *tp2[1000];
	double v1,v2,diff,maks;
	int tn1,tn2,sl1,sl2,i;
	fgets(s,MAXL,stdin);
	split(s,t1,tp1,&tn1);
	split(sol[caseno],t2,tp2,&tn2);
	if(tn1!=tn2) return 0;
	sscanf(tp1[0],"%lf",&v1);
	sscanf(tp2[0],"%lf",&v2);
	maks=fabs(v1)>fabs(v2)?fabs(v1):fabs(v2);
	diff=fabs(v1-v2);
	if(diff>EPS && diff/maks>EPS) return 0;
	/* find slash */
	sl1=sl2=-1;
	for(i=0;i<tn1;i++) if(!strcmp(tp1[i],"/")) sl1=i;
	for(i=0;i<tn2;i++) if(!strcmp(tp2[i],"/")) sl2=i;
	if(sl1!=sl2) return 0;
	if(sl1<0) {
		sorter(tp1,1,tn1);
		sorter(tp2,1,tn2);
	} else {
		sorter(tp1,1,sl1);
		sorter(tp2,1,sl2);
		sorter(tp1,sl1+1,tn1);
		sorter(tp2,sl2+1,tn2);
	}
	for(i=1;i<tn1;i++) if(strcmp(tp1[i],tp2[i])) return 0;
	return 1;
}

int main() {
	int caseno=0;
	init();
	readdef();
	/* read input first, then read output from participant */
	readcases();
	while(cases--) {
		if(!compare(caseno)) {
			puts("-1");
			return 0;
		}
		caseno++;
	}
	puts("1");
	return 0;
}
