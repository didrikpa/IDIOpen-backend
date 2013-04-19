import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.util.Arrays;
import java.util.Scanner;


public class G {
	
	public void go() throws IOException {
		int N = sc.nextInt();
		int M = sc.nextInt();
		int R = sc.nextInt();
		int[] e = new int[N];
		for (int i=0; i<N; i++) e[i] = sc.nextInt();
		
		boolean[][][] possible = new boolean[N+1][M+1][R+1];
		possible[0][0][0] = true;
		int[][][] energy = new int[N+1][M+1][R+1];
		for (int n=1; n<=N; n++) {
			for (int m=0; m<=M; m++) {
				energy[n][m][0] = 0;
				for (int r=0; r<=R; r++) {
					if (!possible[n-1][m][r]) continue;
					energy[n][m][0] = Math.max(energy[n][m][0], energy[n-1][m][r]);
					possible[n][m][0] = true;
				}
				if (m<1) continue;
				for (int r=1; r<=R; r++) {
					if (!possible[n-1][m-1][r-1]) continue;
					energy[n][m][r] = energy[n-1][m-1][r-1] + e[n-1]*r;
					possible[n][m][r] = true;
				}
			}
		}
		
		boolean poss = false;
		int max = 0;
		for (int r=0; r<=R; r++) {
			if (!possible[N][M][r]) continue;
			poss = true;
			if (energy[N][M][r] > max) max = energy[N][M][r];
		}
		if (!poss) System.out.println("impossible");
		else System.out.println(max);
	}
	
	
	static BufferedReader br = new BufferedReader(new InputStreamReader(System.in));
	static Scanner sc = new Scanner(System.in);
	public static void main(String[] args) throws NumberFormatException, IOException {
		int T = sc.nextInt();
		while(T-->0) {
			new G().go();
		}
	}
}
