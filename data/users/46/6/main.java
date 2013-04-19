import java.util.Scanner;


public class main {

	public static void main(String[] args){
		Scanner in = new Scanner(System.in);
		int testCases = in.nextInt();

		for(int i = 0; i < testCases; i++){
			int N = in.nextInt();
			int M = in.nextInt();
			int R = in.nextInt();
			int[][] V = new int[N][R];
			int[][] D = new int[N][M+1];
			int[] E = new int[N];
			boolean[][] accessible = new boolean[N][M+1];
			for(int j = 0; j< N; j++){
				E[j] = in.nextInt();
			}
			for(int j = 0; j < N; j++){
				for(int k = 0; k < R;k++){
					if(k>j){
						break;
					}
					else if(k == 0){
						V[j][k] = E[j];
					}
					else{
						V[j][k] = V[j-1][k-1] + (k+1)*E[j];
					}
				}
			}
			for(int j = 0; j < N; j++){
				accessible[j][0] = true;
				for(int k = 1; k < R+1; k++){
					if(j - k < -1){
						break;
					}
					if(k == M+1){
						break;
					}
					D[j][k] = V[j][k-1];
					accessible[j][k] = true;
				}
			}
			for(int j = 1; j < N; j++){
				for(int k = 1; k < M+1; k++){
					if(accessible[j-1][k]){
						accessible[j][k] = true;
						D[j][k] = Math.max(D[j-1][k], D[j][k]);
					}

					for(int l = 0; l < k; l++){
						if(!(l < R)){
							break;
						}
						if(j-2-l < 0){
							break;
						}
						if(accessible[j-2-l][k-1-l]){
							accessible[j][k] = true;
							if(D[j][k] < D[j-2-l][k-1-l] + V[j][l]){
								D[j][k] = D[j-2-l][k-1-l] + V[j][l];
							}

						}



					}
				}
			}
			if(!accessible[N-1][M]){
				System.out.println("impossible");
			}
			else{
				System.out.println(D[N-1][M]);
			}
		}
	}
}
