import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.util.Stack;
import java.util.StringTokenizer;

/**
 * User: eireksten
 * Date: 4/17/13
 * Time: 6:26 PM
 */
public class neurotic_er {

    public static BufferedReader stdin = new BufferedReader(new InputStreamReader(System.in));
    public static StringTokenizer st;
    private static final long MOD = 1000000007L;

    public static String TOKEN() throws IOException {
        while(st == null || !st.hasMoreTokens())st = new StringTokenizer(stdin.readLine());
        return st.nextToken();
    }

    public static int INT() throws IOException {
        return Integer.parseInt(TOKEN());
    }

    public static double DOUBLE() throws IOException {
        return Double.parseDouble(TOKEN());
    }

    public static void main(String[] args) throws Exception {

        int T = INT();

        while(T-- > 0) {
            int N = INT();

            int[] downstream = new int[N];
            long[] weight = new long[N];

            downstream[0] = -1;
            for(int i = 1;i < N; i++)downstream[i] = INT();
            for(int i = 1; i < N; i++)weight[i] = INT();

            int[] prevs = new int[N];
            for(int i = 1; i < N; i++)  {
                prevs[downstream[i]]++;
            }
            int[][] from = new int[N][];
            for(int i = 0; i < N; i++)from[i] = new int[prevs[i]];

            for(int i = 1; i < N; i++) {
                int to = downstream[i];
                from[to][--prevs[to]] = i;
            }

            new neurotic_er().solve(from, downstream, weight);

        }

    }

    private void solve(int[][] from, int[] to, long[] weight) {

        Stack<Integer> toprocess = new Stack<Integer>();

        toprocess.push(0);

        int N = from.length;
        long[] value = new long[N];
        boolean[] odd = new boolean[N];

        for(int i = 0; i < N; i++) {
            if(from[i].length == 0) {
                value[i] = 1;
                odd[i] = true;
            }
        }

        while(!toprocess.isEmpty()) {
            int next = toprocess.peek();

            if(value[next] != 0) {
                // Finish this node!
                if(next == 0)break;

                toprocess.pop();

                value[to[next]] += weight[next] * value[next];
                value[to[next]] %= MOD;

                if((weight[next] % 2) != 0 && odd[next])odd[to[next]] = !odd[to[next]];

            } else {
                // Process upstream first!
                for(int fr : from[next])
                    toprocess.push(fr);
            }

        }

        if(!odd[0]) {
            System.out.println("FREAK OUT");
        } else {
            System.out.println(value[0]);
        }
    }
}
