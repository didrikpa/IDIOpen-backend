import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;


public class H {
	
	public void go() throws IOException {
		String s = br.readLine().trim();
		if (s.isEmpty()) {
			System.out.println("invalid input");
			return;
		}
		String num = "";
		boolean numFound = false;
		for(char c : s.toCharArray()) {
			if (c<'0' || c>'9') {
				System.out.println("invalid input");
				return;
			}
			if (c=='0') {
				if (!numFound) continue;
			} else numFound = true;
			num += c;
		}
		if (!s.isEmpty() && num.isEmpty()) System.out.println(0);
		else if (num.isEmpty()) System.out.println("invalid input");
		else System.out.println(num);
	}
	
	
	static BufferedReader br = new BufferedReader(new InputStreamReader(System.in));
	public static void main(String[] args) throws NumberFormatException, IOException {
		int T = Integer.parseInt(br.readLine());
		while(T-->0) {
			new H().go();
		}
	}
}
