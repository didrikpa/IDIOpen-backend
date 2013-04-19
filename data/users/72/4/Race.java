import java.util.Scanner;

public class Race {

    public static void main(String[] args) {

        try {

            Scanner scan = new Scanner(System.in);
            int n = Integer.parseInt(scan.nextLine());
            
            for (int i = 0; i < n; i++) {
                int N = scan.nextInt();
                int S = scan.nextInt();
                scan.nextLine();
                int[] v = new int[N];
                for (int j = 0; j < N; j++) {
                    v[j] = scan.nextInt();
                }
                scan.nextLine();

                double[] tid = new double[N];

                for (int k = 0; k < N; k++) {
                    tid[k] = S / (double) v[k];
                }

                int runder = 1;
                for (int l = 1; l < N; l++) {
                    if (tid[l] + 1 < tid[l-1]) {
                        runder++;
                    }
                }
                System.out.println(runder);

            }

        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}
