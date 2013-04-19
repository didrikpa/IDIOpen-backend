#include <string.h>
#include <stdio.h>

#define bsz (BUFSIZ*100)

char in_buffer[bsz];
char out_buffer[bsz];
int box[100*1000];

char output[100 * 30*1000 * (1 + 20)];
char* offset;

void solve(void) {
    int B, P, Q, i, j, a, PQ, sum;
    char c;

    scanf(" %d %d %d", &B, &P, &Q);

    bzero(box, B*sizeof(int));

    PQ = P + Q;
    while (PQ--) {
        scanf(" %c %d %d", &c, &i, &j);

        if (c == 'P') {
            box[i-1] += j;
        } else { 
            sum = 0;
            while (i <= j) {
                sum += box[i-1];
                i++;
            }
            printf("%d\n", sum);
            //offset += sprintf(offset, "%d\n", sum);
        }
    }
}

int main(int argc, char** argv) {
    int T;

    setvbuf(stdout, out_buffer, _IOFBF, bsz);
    setvbuf(stdin, in_buffer, _IOFBF, bsz);

    scanf("%d", &T);

    //output[0] = '\0';
    //offset = output;
    while (T--) solve();

    //puts(output);
    return 0;
}
