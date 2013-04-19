import java.io.*;
import java.util.*;

public class frenzy_er_mle {

	static BufferedReader stdin = new BufferedReader(new InputStreamReader(System.in));
	static StringTokenizer st;

	static String TOKEN() throws Exception {
		while (st == null || !st.hasMoreTokens())st = new StringTokenizer(stdin.readLine());
		return st.nextToken();
	}
	static int INT() throws Exception {return Integer.parseInt(TOKEN());}
	
	public static void main(String[] args) throws Exception {
		int cases = INT();
		while(cases-->0) {
			int W = INT(), H = INT();
			char[][] grid = new char[W][];
			for(int i = 0;i<W;i++)grid[i] = TOKEN().toCharArray();
			int sol = new frenzy_er_mle().go(grid);
			if(sol==-1)System.out.println("Impossible");
			else System.out.println(sol);
		}
	}
	
	int[][] dists;
	
	public int go(char[][] level) throws Exception {
		int W = level.length, H = level[0].length;
		
		// 1. Add information on the grid:
		int N = 0;
		int[] ulgr = null;
		int[][] grid = new int[W][H];
		for(int i = 0;i<W;i++) {
			for(int j = 0;j<H;j++) {
				grid[i][j] = -1;
				if(level[i][j]=='U')ulgr = new int[] {i,j};
				if(level[i][j]=='X')grid[i][j] = -2;
				if(level[i][j]=='#')grid[i][j] = N++;
			}
		}
		
		// 2. Solve using breadth-first search:
		return solve(grid, ulgr[0], ulgr[1], N);
		
	}
	
	int[] xmove = new int[] {-1,1,0,0};
	int[] ymove = new int[] {0,0,-1,1};
	
	public int solve(int[][] grid, int x, int y, int N) {
		
		int W = grid.length, H = grid[0].length;
		
		// Initiating BFS!
		LinkedList<int[]> queue = new LinkedList<int[]>();
		queue.add(new int[] {x,y,0});
		
		int[][][] distances = new int[W][H][1<<N];
		for(int i = 0;i<W;i++)for(int j = 0;j<H;j++)
				Arrays.fill(distances[i][j], Integer.MAX_VALUE/2);
		
		distances[x][y][0] = 0;
		
		// BFS through all possible states!
		while(!queue.isEmpty()) {
			int[] next = queue.poll();
			int nextdist = distances[next[0]][next[1]][next[2]]+1;
			
			// Move to neighbouring location:
			for(int i = 0;i<xmove.length;i++) {
				int xx = next[0]+xmove[i], yy = next[1]+ymove[i];
				if(xx<0 || xx>=W || yy<0 || yy>=H)continue;
				if(grid[xx][yy]==-2)continue;
				if(distances[xx][yy][next[2]]<Integer.MAX_VALUE/2)continue;
				distances[xx][yy][next[2]] = nextdist;
				queue.add(new int[] {xx,yy,next[2]});
			}
			
			// Eat a sheep:
			if(grid[next[0]][next[1]]>=0) {
				int map = next[2]|(1<<grid[next[0]][next[1]]);
				if(map==((1<<N)-1))return nextdist;
				if(distances[next[0]][next[1]][map]>=Integer.MAX_VALUE/2) {
					distances[next[0]][next[1]][map] = nextdist;
					queue.add(new int[] {next[0],next[1],map});
				}
			}
		}
		return -1;
		
	}

}
