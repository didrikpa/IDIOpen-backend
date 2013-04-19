import java.io.*;
import java.util.*;
import java.util.regex.*;

public class problemC {
	static BufferedReader stdin = new BufferedReader(
	new InputStreamReader(System.in));
	static StringTokenizer st = new StringTokenizer("");

	public static void main(String[] args) throws Exception {
		int inputLength = readInt();
		int i = 0;
		String output = "";
		while(i < inputLength){
				i = i+1;
				String s = readString();
				int n = findMin(s);
				System.out.println(Integer.toString(n));
				output = output.concat(Integer.toString(n)+"\n");
		}
		//System.out.print(output);
	}
	public static int findMin(String s){
		boolean b = Pattern.matches("l"+"[a-z]"+"l", s);
		if(s.contains("lol")){
			return 0;
		}else if(b||s.contains("ll")||s.contains("lo")||s.contains("ol")){
				return 1;
		}else if(s.contains("l")||s.contains("o")){
			return 2;
		}else{return 3;}
	}

	// Read next input-token as string.
	static String readString() throws Exception {
		while (!st.hasMoreTokens()) {
		st = new StringTokenizer(stdin.readLine());
		}
		return st.nextToken();
	}

	// Read next input-token as integer.
	static int readInt() throws Exception {
		return Integer.parseInt(readString());
	}

	// Read next input-token as double.
	static double readDouble() throws Exception {
		return Double.parseDouble(readString());
	}
}
