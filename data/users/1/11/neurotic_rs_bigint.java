import java.io.*;
import java.util.*;
import java.math.*;

public class neurotic_rs_bigint {
	BufferedReader in=new BufferedReader(new InputStreamReader(System.in));
	StringTokenizer st=new StringTokenizer("");
	String LINE() throws Exception { return in.readLine(); }
	String STR() throws Exception {
		while(!st.hasMoreTokens()) st=new StringTokenizer(LINE());
		return st.nextToken();
	}
	int INT() throws Exception { return Integer.parseInt(STR()); }
	public static void main(String[]a) throws Exception {
		new neurotic_rs_bigint().go();
	}
	void go() throws Exception {
		int T=INT();
		while(T-->0) solve();
	}
	void solve() throws Exception {
		int MOD=1000000007;
		int n=INT();
		int parent[]=new int[n];
		int weight[]=new int[n];
		boolean leaf[]=new boolean[n];
		parent[0]=-1;
		for(int i=1;i<n;i++) parent[i]=INT();
		weight[0]=0;
		for(int i=1;i<n;i++) weight[i]=INT();
		Arrays.fill(leaf,true);
		for(int i=1;i<n;i++) leaf[parent[i]]=false;

		BigInteger value[]=new BigInteger[n];
		for(int i=0;i<n;i++) value[i]=BigInteger.valueOf(leaf[i]?1:0);

		boolean done=true;
		boolean processed[]=new boolean[n];
		Arrays.fill(processed,false);
		processed[0]=true;
		int indeg[]=new int[n];
		for(int i=1;i<n;i++) indeg[parent[i]]++;
		do {
			done=true;
			for(int i=0;i<n;i++) if(indeg[i]==0 && !processed[i]) {
				int next=parent[i];
				indeg[next]--;
				value[next]=value[next].add(value[i].multiply(BigInteger.valueOf(weight[i])));
				processed[i]=true;
				done=false;
			}
		} while(!done);
		if(value[0].mod(BigInteger.valueOf(2)).equals(BigInteger.ZERO)) System.out.println("FREAK OUT");
		else System.out.println(value[0].mod(BigInteger.valueOf(MOD)));
	}
}
