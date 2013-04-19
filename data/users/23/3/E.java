import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.util.Arrays;
import java.util.Scanner;


public class E {
	
	public void go() throws IOException {
		int N = sc.nextInt();
		int S = sc.nextInt();
		double[] t = new double[N];
		for (int i=0; i<N; i++) t[i] = (S+0.0)/sc.nextInt();
		
		int races = 0;
		int peopleLeft = N;
		boolean[] isDone = new boolean[N];
		while(peopleLeft > 0) {
			double worstT = 0;
			int starter = 0;
			for (int i=0; i<N; i++) {
				if (isDone[i]) continue;
				if (t[i]+starter > worstT) {
					worstT = t[i]+starter;
					isDone[i] = true;
					peopleLeft--;
				}
				starter++;
			}
			races++;
		}
		System.out.println(races);
	}
	
	
	static BufferedReader br = new BufferedReader(new InputStreamReader(System.in));
	static Scanner sc = new Scanner(System.in);
	public static void main(String[] args) throws NumberFormatException, IOException {
		int T = sc.nextInt();
		while(T-->0) {
			new E().go();
		}
	}
}
