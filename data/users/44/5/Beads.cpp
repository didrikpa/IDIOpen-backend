#include <cstdio>
#include <vector>
using namespace std;

//Space used is offset,offset + size of table
const int OFFSET = 101*10000;

void update(int tree[], int i, int v) {
    i += OFFSET;
    tree[i] += v;
    i /= 2;
    while(i > 0) {
        tree[i] = tree[2*i]+tree[2*i+1]; //tree[i] += v
        i /= 2;
    }
}

int query(int tree[], int l, int r) {
    int result = 0;
    l += OFFSET - 1; r+= OFFSET+1;
    while(l/2 != r/2) {
        if(l%2 == 0) result += tree[l^1]; //tree[l+1]
        if(r%2 == 1) result += tree[r^1]; //tree[r-1]
        l /= 2; r /= 2;
    }
    return result;
}


int main(){
	int cases;
	scanf("%d", &cases);
	for(int abc = 0; abc < cases; abc++){
		int boxes, P, Q;
		scanf("%d%d%d", &boxes, &P, &Q);
		int tree[2*OFFSET] = {0};
		char buf[10];
		gets(buf);
		for(int i = 0; i < P+Q; i++){
			gets(buf);
			char f;
			int i, j;
			sscanf(buf, "%c%d%d", &f, &i, &j);
			//printf("%c %d %d\n", f, i, j);
			if(f == 'P'){
				update(tree, i, j);
			}
			else{
				printf("%d\n", query(tree, i, j));
			}
		}
	}
	return 0;
}
