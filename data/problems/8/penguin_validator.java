import java.io.*;
import java.text.*;
import java.util.*;

public class penguin_validator {

	static BufferedReader stdin = new BufferedReader(new InputStreamReader(System.in));
	static StringTokenizer st;

	static String LINE() throws Exception { return stdin.readLine(); }
	static String TOKEN() throws Exception {
		while (st == null || !st.hasMoreTokens())st = new StringTokenizer(LINE());
		return st.nextToken();
	}
	static int INT() throws Exception {return Integer.parseInt(TOKEN());}
	static long LONG() throws Exception {return Long.parseLong(TOKEN());}
	static double DOUBLE() throws Exception {return Double.parseDouble(TOKEN());}

	static DecimalFormat DF = new DecimalFormat("0.000",new DecimalFormatSymbols(Locale.ENGLISH));
	
	public static void main(String[] args) throws Exception {
		ArrayList<String> fasit = getSolution();
		
		LINE();
		int atcase = 0;
		while(stdin.ready()) {
			String nextline = LINE().trim(); 
			if(atcase>=fasit.size()) {
				if(nextline.length()>0) {
					System.out.println("-1");
					return;
				}
				continue;
			} else {
				double distance = Double.parseDouble(fasit.get(atcase));
				
				if(!isDouble(nextline) || !equals(Double.parseDouble(nextline),distance)) {
					System.out.println("-1");
					return;
				}
				
				atcase++;
			}
		}
		
		if(atcase<fasit.size()) {
			System.out.println("-1");
			return;
		}
		
		System.out.println("1");
		
	}
	
	public static final double EPSILON = 1.1e-7;
	
	// Sjekk for om to doubles er like:
	public static boolean equals(double a, double b) {
		if(Math.abs(a-b)<EPSILON)return true;
		double min = b-(b*EPSILON); double max = b+(b*EPSILON);
		if(min>max) { double temp = min; min = max; max = temp; }
		if(a>min && a<max)return true;
		return false;
	}
	
	public static boolean isDouble(String value) {
		try {
			Double.parseDouble(value);
		} catch (NumberFormatException e) {
			return false;
		}
		return true;
	}
	
	public static boolean isInteger(String value) {
		try {
			Integer.parseInt(value);
		} catch (NumberFormatException e) {
			return false;
		}
		return true;
	}
	
	public static ArrayList<String> getSolution() throws Exception {
		ArrayList<String> ret = new ArrayList<String>();
		
		int cases = INT();
		
		while(cases-->0) {
			int N = INT(), W = INT();
			int[] profile = new int[N+1];
			for(int i = 0;i<profile.length;i++) {
				profile[i] = INT();
			}
			
			ret.add(Double.toString(solve(profile, W)));
		}
		
		return ret;
	}
	
	public static double solve(int[] profile, int water) {
		double max = 100.0;
		double min = 0.0;
		int iterations = 50;
		while(iterations-->0) {
			double mid = min + (max-min)/2.0;
			double needed = waterNeeded(profile, mid);
			if(needed>water)min = mid;
			else max = mid;
			
		}
		return max;
	}
	
	public static double waterNeeded(final int[] profile, final double maxHeight) {
		double[] waterLevel = new double[profile.length];
		
		// Filling the track!
		double lastwl = 0.0;
		// Left-to-right
		for(int i = 0;i<profile.length;i++) {
			if(lastwl>profile[i])waterLevel[i] = lastwl;
			else if(i==0 || profile[i]>=profile[i-1])lastwl = profile[i] - maxHeight;
		}
		lastwl = 0.0;
		// Right-to-left
		for(int i = profile.length-1;i>=0;i--) {
			if(lastwl>profile[i] && lastwl>waterLevel[i])waterLevel[i] = lastwl;
			else if(i==profile.length-1 || profile[i]>=profile[i+1])lastwl = profile[i]-maxHeight;
		}
		
		// Calculating amount of water:
		double water = 0.0;
		for(int i = 0;i<profile.length-1;i++) {
			water += usedWater(profile[i], profile[i+1],Math.max(waterLevel[i], waterLevel[i+1]));
		}
		return water;
		
	}
	
	public static double usedWater(final int ha, final int hb, final double waterLevel) {
		double max = ha>hb?ha:hb, min = ha<hb?ha:hb;
		if(waterLevel<=min)	return 0.0;
		if(waterLevel>max) 	return waterLevel - max + (max-min)/2;
		return (waterLevel-min)*(waterLevel-min)/(2*(max-min));
	}

}
