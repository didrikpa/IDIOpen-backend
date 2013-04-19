import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;


public class C {
	
	public void go() throws IOException {
		char[] s = br.readLine().toCharArray();
		int min = 3;
		for(int i=0; i<s.length; i++) {
			if (i<s.length-2) {
				if (s[i]=='l' && s[i+1]=='o' && s[i+2]=='l') min = 0;
				if (s[i]=='l' && s[i+2]=='l') min = Math.min(min,1);			
			}
			if (i<s.length-1) {
				if (s[i]=='l' && s[i+1]=='o') min = Math.min(min, 1);
				if (s[i]=='l' && s[i+1]=='l') min = Math.min(min,1);
				if (s[i]=='o' && s[i+1]=='l') min = Math.min(min,1);				
			}
			if (i<s.length) {
				if (s[i]=='l' || s[i]=='o') min = Math.min(min, 2);
			}
		}
		System.out.println(min);
	}
	
	
	static BufferedReader br = new BufferedReader(new InputStreamReader(System.in));
	public static void main(String[] args) throws NumberFormatException, IOException {
		int T = Integer.parseInt(br.readLine());
		while(T-->0) {
			new C().go();
		}
	}
}
