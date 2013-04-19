import java.util.ArrayList;
import java.util.Scanner;


public class ProblemE {

	public static int t;
	
	public static void main(String[] args) {
		Scanner tast = new Scanner(System.in);
		t = tast.nextInt();
		while(t-- > 0) {
			
			int racers = tast.nextInt();
			int length = tast.nextInt();
			
			ArrayList<Integer> speeds = new ArrayList<Integer>();
			
			for(int i=0; i< racers; ++i){
				speeds.add(new Integer(tast.nextInt()));
			}
			
			
			int runs = 0;
			while(speeds.size()>0){
				double maksTid=0;
				int starttid = 0;
				runs++;
				ArrayList<Integer> newArray = new ArrayList<Integer>();
				for(Integer t: speeds){
					double newTime = starttid+(length/t.doubleValue());
					if(newTime>maksTid){
						maksTid=newTime;
					}else{
						newArray.add(t);
					}
					starttid++;
				}
				speeds=newArray;
			}
			
			System.out.println(runs);
		}
	}
}
