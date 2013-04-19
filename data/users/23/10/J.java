import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.util.Scanner;


public class J {
	
	public void go() throws IOException {
		int N = sc.nextInt();
		int[] order = new int[N];
		for (int i=0; i<N; i++) order[i] = sc.nextInt();
		
		int[][] cost = new int[N][N];
		for (int i=0; i<N; i++) 
			for (int j=0; j<N; j++)
				cost[i][j] = sc.nextInt();
		
		for (int k=0; k<N; k++)
			for (int i=0; i<N; i++) {
				if (cost[i][k] == -1) continue;
				for (int j=0; j<N; j++) {
					if (cost[k][j] == -1) continue;
					int d = cost[i][k] + cost[k][j];
					if (d >= cost[i][j] && cost[i][j]!=-1) continue;
					cost[i][j] = d;
				}
			}
		
		int dist = 0;
		for (int i=0; i<N; i++) {
			int city1 = order[i];
			int city2 = order[(i+1)%N];
			int d = cost[city1][city2];
			if (d == -1) {
				System.out.println("impossible");
				return;
			}
			dist += d;
		}
		System.out.println(dist);
	}
	
	
	static BufferedReader br = new BufferedReader(new InputStreamReader(System.in));
	static Scanner sc = new Scanner(System.in);
	public static void main(String[] args) throws NumberFormatException, IOException {
		int T = sc.nextInt();
		while(T-->0) {
			new J().go();
		}
	}
}
