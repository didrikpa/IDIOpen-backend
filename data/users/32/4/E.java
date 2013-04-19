
import java.util.Scanner;
import java.util.LinkedList;
import java.util.*;

class E {

    public static int min(int a, int b) {
        return (a < b) ? a : b;
    }

    public static void main(String [] a) {
        Scanner s = new Scanner(System.in);

        int T = s.nextInt();

        for (int i = 0; i < T; i++) {
            int N, S;
            N = s.nextInt();
            S = s.nextInt();

            LinkedList<Integer> v = new LinkedList<Integer>();
            for (int j = 0; j < N; j++) {
                v.addLast(s.nextInt());
            }


            int race = 0;
            while (! v.isEmpty()) {
                race++;
                Iterator<Integer> it = v.iterator();

                double t = ((double)S) / it.next();
                it.remove();
                int offset = 1;
                while (it.hasNext()) {
                    int d = it.next();
                    double time =  ((double)S/d) + offset++;
                    if (time > t) {
                       it.remove();
                       t = time;
                    } else {
                    } 
                }
            }
            System.out.println(race);
        }
    }
}
