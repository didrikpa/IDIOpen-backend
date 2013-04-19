import java.io.*;
import java.util.*;

public class LOL {
static BufferedReader stdin = new BufferedReader(
new InputStreamReader(System.in));
static StringTokenizer st = new StringTokenizer("");

public static void main(String[] args) throws Exception {
	int words = readInt();
	ArrayList<Integer> lolwords = new ArrayList<Integer>();
	if (words > 100) {
		words = 100;
	}
	for(int i = 0; i < words; i++){
		String word = readString();
		if(word.length() <= 50) { lolwords.add(addLol(word)); }
	}
	for (int i = 0; i < lolwords.size(); i++) {
		System.out.println(lolwords.get(i));
	}
}

public static int addLol(String word){
	if(word.contains("lol")) { return 0; }
	else if(word.contains("lo") || word.contains("ol") || word.contains("ll")) { return 1; }
	else if(word.matches("\\w*l[\\w{1}]l\\w*")) { return 1; }
	else if(word.contains("l") || word.contains("o")) { return 2; }
	else { return 3; }
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