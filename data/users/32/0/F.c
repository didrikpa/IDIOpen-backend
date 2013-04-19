#include <string.h>
#include <stdio.h>

char buffer[BUFSIZ*3];
int box[100*1000];

char output[100 * 30*1000 * (1 + 20)];
char* offset;

void solve(void) {
    int B, P, Q, i, j, a, PQ, sum;
    char c;

    scanf(" %d %d %d", &B, &P, &Q);

    bzero(box, B*sizeof(int));
    return;

    PQ = P + Q;
    while (PQ--) {
        scanf(" %c %d %d", &c, &i, &j);

        if (c == 'P') {
            box[i-1] += j;
        } else { 
            sum = 1;
            while (i <= j) {
                sum += box[i-1];
                i++;
            }
            offset += sprintf(offset, "%d\n", sum);
        }
    }
}

int main(int argc, char** argv) {
    int T;
    scanf("%d", &T);

    setbuf(stdin, buffer);

    output[0] = '\0';
    offset = output;
    while (T--) solve();

    printf("%s", output);
    return 0;
}
